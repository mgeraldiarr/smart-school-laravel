<?php

namespace Tests\Feature\Admin;

use App\Models\SchoolParent;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use App\Support\SpreadsheetReader;
use DateTimeImmutable;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use OpenSpout\Common\Entity\Cell;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\XLSX\Writer;

class ImportTest extends AdminTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->signInAsAdmin();
    }

    /** @param list<list<mixed>> $rows baris data tanpa judul */
    private function xlsx(array $header, array $rows): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'uji').'.xlsx';
        $writer = new Writer();
        $writer->openToFile($path);
        $writer->addRow(Row::fromValues($header));
        foreach ($rows as $values) {
            $writer->addRow(new Row(array_map(fn ($v) => $v instanceof Cell ? $v : Cell::fromValue($v), $values)));
        }
        $writer->close();

        return new UploadedFile($path, 'data.xlsx', null, null, true);
    }

    private function csv(string $content): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'uji');
        file_put_contents($path, $content);

        return new UploadedFile($path, 'data.csv', 'text/csv', null, true);
    }

    public function test_template_contains_header_and_examples_in_import_order(): void
    {
        $response = $this->get('/admin/import/siswa/template')->assertOk();
        $this->assertSame('attachment; filename=template-impor-siswa.xlsx', $response->headers->get('content-disposition'));

        $rows = iterator_to_array((new SpreadsheetReader())->rows($response->getFile()->getPathname(), 'xlsx'));

        $this->assertSame(['NIS', 'Nama siswa', 'Kelas', 'Tanggal lahir', 'Nama wali', 'Email wali'], $rows[1]);
        $this->assertSame('20241010', $rows[2][0]);
    }

    public function test_unchanged_template_imports_its_example_rows(): void
    {
        $template = $this->get('/admin/import/guru/template')->getFile()->getPathname();
        $upload = new UploadedFile($template, 'template.xlsx', null, null, true);

        $this->post('/admin/import/guru', ['file' => $upload])
            ->assertRedirect()
            ->assertSessionHas('import_result', fn ($r) => $r['created'] === 2 && $r['failures'] === []);

        // NUPTK 16 digit harus tersimpan utuh, bukan dibulatkan Excel.
        $teacher = Teacher::where('nuptk', '1234567890123456')->firstOrFail();
        $this->assertSame('teacher', $teacher->user->role);
        $this->assertSame('1234567890123456', $teacher->user->username);
        $this->assertTrue(Hash::check('password123', $teacher->user->password));
    }

    public function test_student_xlsx_accepts_excel_numbers_and_date_formats_and_reports_bad_rows(): void
    {
        $dateCell = Cell::fromValue(new DateTimeImmutable('2013-03-04'), (new Style())->withFormat('yyyy-mm-dd'));

        $file = $this->xlsx(['NIS', 'Nama', 'Kelas', 'Lahir', 'Wali', 'Email'], [
            [20250001, 'Satu', '7-D', $dateCell, 'Wali Satu', 'WALI.SATU@example.com'],
            ['20250002', 'Dua', '7-D', '04/05/2013', 'Wali Dua', 'wali.dua@example.com'],
            ['20250003', 'Tiga', '7-D', 41337, 'Wali Tiga', 'wali.tiga@example.com'],
            ['20250004', 'Empat', '7-D', '2013-01-01', 'Wali Empat', 'bukan-email'],
            ['', '', '', '', '', ''],
            ['20250005', 'Lima', '7-D', '31/31/2013', 'Wali Lima', 'wali.lima@example.com'],
        ]);

        $this->post('/admin/import/siswa', ['file' => $file])->assertSessionHas('import_result', function ($result) {
            $this->assertSame(3, $result['created']);
            $this->assertSame([5, 7], array_column($result['failures'], 'row'));
            $this->assertSame('Email wali harus berupa alamat email yang valid.', $result['failures'][0]['message']);
            $this->assertStringContainsString('tidak dikenali', $result['failures'][1]['message']);

            return true;
        });

        $this->assertSame('2013-03-04', Student::where('nis', '20250001')->value('birth_date'));
        $this->assertSame('2013-05-04', Student::where('nis', '20250002')->value('birth_date'));
        $this->assertSame('2013-03-04', Student::where('nis', '20250003')->value('birth_date'));
        $this->assertSame('7', Student::where('nis', '20250001')->first()->schoolClass->grade);
        $this->assertTrue(User::where('email', 'wali.satu@example.com')->where('role', 'parent')->exists());
    }

    public function test_semicolon_csv_from_indonesian_excel_is_supported(): void
    {
        $file = $this->csv("NIS;Nama;Kelas;Lahir;Wali;Email\n20250010;Csv Satu;9-A;2011-02-03;Ibu Csv;ibu.csv@example.com\n");

        $this->post('/admin/import/siswa', ['file' => $file])
            ->assertSessionHas('import_result', fn ($r) => $r['created'] === 1);

        $this->assertDatabaseHas('students', ['nis' => '20250010', 'full_name' => 'Csv Satu']);
    }

    public function test_reimport_updates_existing_rows_and_keeps_parent_password(): void
    {
        $content = "NIS,Nama,Kelas,Lahir,Wali,Email\n20250020,Nama Lama,8-A,2011-02-03,Wali,wali@example.com\n";
        $this->post('/admin/import/siswa', ['file' => $this->csv($content)]);

        $parent = User::where('email', 'wali@example.com')->first();
        $parent->update(['password' => 'sandi-baru-ortu']);

        $this->post('/admin/import/siswa', ['file' => $this->csv(str_replace('Nama Lama', 'Nama Baru', $content))])
            ->assertSessionHas('import_result', fn ($r) => $r['created'] === 0 && $r['updated'] === 1);

        $this->assertSame(1, Student::count());
        $this->assertSame('Nama Baru', Student::first()->full_name);
        $this->assertTrue(Hash::check('sandi-baru-ortu', $parent->fresh()->password));
    }

    public function test_siblings_share_one_parent_account(): void
    {
        $content = "NIS,Nama,Kelas,Lahir,Wali,Email\n"
            ."20250030,Kakak,9-A,2011-02-03,Ibu Sama,ibu@example.com\n"
            ."20250031,Adik,7-A,2013-02-03,Ibu Sama,ibu@example.com\n";

        $this->post('/admin/import/siswa', ['file' => $this->csv($content)]);

        $this->assertSame(1, SchoolParent::count());
        $this->assertSame(2, SchoolParent::first()->students()->count());
    }

    public function test_parent_email_owned_by_a_teacher_is_rejected_per_row(): void
    {
        $this->makeTeacher(email: 'guru@example.com');

        $this->post('/admin/import/siswa', ['file' => $this->csv("NIS,Nama,Kelas,Lahir,Wali,Email\n20250040,Siswa,8-A,2011-02-03,Wali,guru@example.com\n")])
            ->assertSessionHas('import_result', fn ($r) => $r['created'] === 0
                && $r['failures'][0]['message'] === 'Email guru@example.com sudah dipakai akun guru.');

        // Transaksi per baris: siswanya juga tidak tersimpan setengah jadi.
        $this->assertDatabaseMissing('students', ['nis' => '20250040']);
    }

    public function test_teacher_import_rejects_email_used_by_another_account(): void
    {
        $this->makeTeacher('9999888877776666', 'dipakai@example.com');

        $this->post('/admin/import/guru', ['file' => $this->csv("NUPTK,Nama,Email,HP\n1234,Guru Baru,dipakai@example.com,\n12ab,Guru Huruf,huruf@example.com,\n")])
            ->assertSessionHas('import_result', function ($r) {
                $this->assertSame('Email dipakai@example.com sudah dipakai akun lain.', $r['failures'][0]['message']);
                $this->assertSame('NUPTK "12ab" hanya boleh berisi angka.', $r['failures'][1]['message']);

                return true;
            });
    }

    public function test_rejects_other_file_types_and_unknown_import_types(): void
    {
        $this->post('/admin/import/siswa', ['file' => UploadedFile::fake()->create('data.xls', 10)])
            ->assertSessionHasErrors('file');

        $this->get('/admin/import/kelas')->assertNotFound();
    }

    public function test_corrupt_xlsx_shows_a_readable_error(): void
    {
        $this->post('/admin/import/siswa', ['file' => UploadedFile::fake()->createWithContent('data.xlsx', 'bukan excel')])
            ->assertSessionHasErrors(['file' => 'Berkas tidak bisa dibaca. Pastikan isinya mengikuti template dan berkas tidak rusak, lalu coba lagi.']);
    }
}
