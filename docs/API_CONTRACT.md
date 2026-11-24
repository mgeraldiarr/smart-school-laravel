# Kontrak API Smart School

Kesepakatan bentuk request dan respons antara backend Laravel dan aplikasi Flutter.
**Ubah dokumen ini lewat PR sebelum mengubah kode endpoint.** Backend dan mobile
boleh mengerjakan endpoint yang sama secara paralel setelah bagiannya disetujui.

Status tiap endpoint:

- **Ada**: sudah berjalan di `main`.
- **Ubah**: sudah ada, tapi respons atau aturannya akan berubah.
- **Baru**: belum ada, harus dibuat.
- **Usang**: masih berjalan selama masa peralihan, jangan dipakai untuk fitur baru.

Dasar keputusan: dokumen tim "Peta Integrasi Smart School" (keputusan 24 November 2026).

---

## 1. Konvensi umum

### Alamat dan autentikasi

- Base URL: `{API_URL}/api`. Di mobile diatur lewat `--dart-define=API_URL=...`.
- Endpoint terlindungi memakai header `Authorization: Bearer {token}` (Laravel Sanctum).
- Header wajib: `Accept: application/json`.

### Format respons

Berhasil:

```json
{ "status": "success", "message": "Teks untuk pengguna (opsional)", "data": { } }
```

Gagal:

```json
{ "status": "error", "message": "Teks yang bisa langsung ditampilkan ke pengguna" }
```

Gagal validasi (422 dari `validate()` Laravel) menambahkan `errors`:

```json
{ "message": "...", "errors": { "password": ["Minimal 8 karakter."] } }
```

`message` selalu dalam Bahasa Indonesia dan aman ditampilkan apa adanya di aplikasi.

### Kode HTTP

| Kode | Arti | Yang dilakukan aplikasi |
|---|---|---|
| 200 / 201 | Berhasil / data baru dibuat | Tampilkan hasil |
| 401 | Token tidak valid atau kedaluwarsa, atau login gagal | Hapus sesi, kembali ke Login (kecuali di layar login: tampilkan `message`) |
| 403 | Peran salah, akun nonaktif, atau bukan data miliknya | Tampilkan `message` |
| 404 | Data tidak ditemukan | Tampilkan `message` |
| 409 | Data sudah ada (misal siswa sudah diabsen) | Tampilkan `message`, bukan sebagai error fatal |
| 422 | Isian salah atau aturan bisnis dilanggar (misal di luar jam pelajaran) | Tampilkan `message` / `errors` |
| 429 | Terlalu banyak percobaan | Tampilkan `message`, minta coba lagi nanti |
| 5xx | Kesalahan server | Tampilkan pesan umum, boleh coba lagi |

### Format data

| Jenis | Format | Contoh |
|---|---|---|
| Penamaan field | `snake_case` | `student_name` |
| Tanggal | `YYYY-MM-DD` | `2026-11-23` |
| Jam | `HH:mm` (tampilan) atau `HH:mm:ss` (jam tercatat) | `07:30`, `07:05:12` |
| Bulan | `YYYY-MM` | `2026-11` |
| Zona waktu | Semua tanggal/jam dalam WIB (`Asia/Jakarta`) | |
| Nilai | angka desimal 0–100 | `87.5` |
| Hari | `Senin`, `Selasa`, `Rabu`, `Kamis`, `Jumat`, `Sabtu` | |

### Daftar nilai resmi (enum)

Aplikasi wajib memetakan nilai ini di **satu tempat**, bukan di tiap halaman.

| Nama | Nilai | Keterangan |
|---|---|---|
| Peran (`role`) | `teacher`, `parent`, `admin` | |
| Status presensi siswa | `Hadir`, `Sakit`, `Izin`, `Alpa`, `Belum_Absen` | `Belum_Absen` hanya muncul di respons, tidak pernah disimpan |
| Metode presensi | `face_scan`, `manual_teacher`, `auto_close` | `auto_close` = diisi Alpa otomatis oleh server (**Baru**) |
| Status kehadiran guru | `Hadir`, `Berhalangan`, `Digantikan`, `Belum_Konfirmasi` | `Belum_Konfirmasi` hanya di respons |
| Jenis asesmen | `Tugas`, `UTS`, `UAS` | |
| Jenis pengecualian jadwal | `jam_kosong`, `libur` | |

---

## 2. Autentikasi

### POST /auth/login — **Baru** (login terpadu)

Satu kolom isian untuk semua peran. Server mencari secara berurutan: NUPTK guru,
lalu NIS (akun wali), lalu username admin.

Request:

```json
{ "identifier": "198501152010011002", "password": "rahasia123" }
```

Respons 200:

```json
{
  "status": "success",
  "data": {
    "token": "1|abc...",
    "user": { "id": 5, "email": "guru@sekolah.id", "role": "teacher", "must_change_password": true },
    "teacher": { "id": 1, "nuptk": "198501152010011002", "full_name": "Ahmad Hidayat, S.Pd.", "phone_number": null }
  }
}
```

Isi selain `token` dan `user` mengikuti peran:

- `teacher`: objek `teacher` seperti di atas.
- `parent`: objek `parent` (`id`, `full_name`, `phone_number`) dan `student` (`id`, `nis`, `full_name`, `class`, `birth_date`, `is_active`), sama seperti `/auth/parent/login`.
- `admin`: objek `admin` (`id`, `username`, `email`).

Aturan:

- Identifier yang tidak ditemukan dan kata sandi yang salah **sama-sama** menghasilkan 401 dengan pesan `"NUPTK/NIS atau kata sandi salah."`. Jangan bedakan keduanya, supaya orang luar tidak bisa menebak NUPTK/NIS mana yang terdaftar.
- Akun nonaktif: 403.
- Dibatasi `throttle` (misal 5 percobaan per menit per identifier).
- NUPTK, NIS, dan username admin tidak boleh ada yang sama; web admin dan impor Excel wajib menolak nilai yang bentrok.

### POST /auth/teacher/login, POST /auth/parent/login — **Usang**

Masih berjalan sampai aplikasi pindah ke `/auth/login`. Respons sudah menyertakan
`user.must_change_password`.

### GET /auth/me — **Ada**

Respons `data`: `user` (`id`, `email`, `username`, `role`, `must_change_password`) dan
`profile` (guru: data `teachers`; wali: data `school_parents` beserta `students[].school_class`).

### POST /auth/change-password — **Ada**

```json
{ "current_password": "password123", "password": "sandiBaru123", "password_confirmation": "sandiBaru123" }
```

- 200: `data.must_change_password = false`. Sesi di perangkat lain diakhiri.
- 422: kata sandi lama salah, atau sandi baru kurang dari 8 karakter / sama dengan yang lama.

Aplikasi menampilkan layar wajib ganti sandi selama `user.must_change_password` bernilai `true`.

### POST /auth/logout, /auth/forgot-password, /auth/verify-otp, /auth/reset-password — **Ada**

Tidak berubah.

---

## 3. Guru (`role:teacher`)

Guru hanya bisa mengakses jadwal, kelas, siswa, dan nilai miliknya sendiri. Pelanggaran: 403.

### GET /teacher/schedules — **Ubah**

Query opsional: `day` (misal `Senin`) **atau** `date` (`YYYY-MM-DD`, **Baru**).

Dengan `date`, respons juga memuat jadwal saat guru ini menjadi **pengganti**, beserta
status hari itu:

```json
{
  "status": "success",
  "data": [
    {
      "id": 12,
      "class_id": 3, "class_name": "8-A",
      "subject_id": 2, "subject_name": "Matematika",
      "day_of_week": "Senin", "start_time": "07:30", "end_time": "09:00",
      "is_substitute": false,
      "original_teacher_name": null,
      "teacher_attendance_status": "Belum_Konfirmasi",
      "exception": null
    }
  ]
}
```

- `is_substitute`: `true` bila jadwal ini milik guru lain dan guru yang login ditunjuk sebagai pengganti.
- `exception`: `null` atau `{ "type": "jam_kosong" | "libur", "note": "Rapat guru" }`. Jadwal dengan pengecualian tidak bisa dimulai dan tidak menghasilkan Alpa.

Tanpa `date`, respons sama seperti sekarang (field tambahan boleh `null`).

### POST /teacher/attendance/start — **Baru** (Konfirmasi Mulai Mengajar)

Mencatat kehadiran guru dan membuka sesi presensi siswa.

```json
{ "schedule_id": 12 }
```

- 201: `data` = `{ "teacher_attendance_id": 7, "status": "Hadir", "confirmed_at": "07:28:40", "session": { ...sama seperti GET /teacher/attendance/session } }`.
- 409: sudah dikonfirmasi hari ini.
- 422: di luar waktu konfirmasi (**15 menit sebelum mulai** sampai jam selesai), hari berbeda, atau jadwal punya pengecualian.
- 403: bukan jadwal guru ini, dan guru ini juga bukan pengganti yang ditunjuk.

### POST /teacher/attendance/unavailable — **Baru** (Laporan Berhalangan)

```json
{ "schedule_id": 12, "date": "2026-11-23", "reason": "Sakit" }
```

- 201: status kehadiran guru untuk jadwal & tanggal itu menjadi `Berhalangan`. Admin mendapat tugas menunjuk pengganti di web; setelah ditunjuk, statusnya menjadi `Digantikan` dan wali murid menerima notifikasi.
- 422: tanggal sudah lewat, atau sesi sudah dimulai.

### GET /teacher/attendance/session — **Ada**

Query: `schedule_id`, `date` (opsional). Status siswa memakai enum resmi, termasuk `Belum_Absen`.
Pengganti yang ditunjuk juga boleh membuka sesi ini (**Ubah**).

### GET /teacher/attendance/face-embeddings — **Baru**

Data wajah siswa untuk dicocokkan di HP. Query: `schedule_id`. Hanya bisa diambil pada hari jadwal itu.

```json
{
  "status": "success",
  "data": {
    "model_version": "mobilefacenet-v1",
    "students": [
      { "student_id": 21, "nis": "20260001", "name": "Ahmad Rizki", "embeddings": [[0.0132, -0.2210, "..."]] }
    ]
  }
}
```

Siswa yang belum mendaftarkan wajah tidak dikirim. HP menyimpan data ini sementara dan
menghapusnya setelah sesi berakhir.

### POST /attendance/face-scan — **Ada**

```json
{ "student_id": 21, "schedule_id": 12, "confidence_score": 0.91, "captured_photo": null }
```

- 201: tercatat `Hadir`.
- 409: sudah tercatat; `data` = `{ "student_name", "status", "check_in_time" }`.
- 422: di luar jam pelajaran, atau siswa bukan anggota kelas jadwal ini.

`confidence_score` adalah tingkat kecocokan dari HP (0–1). `captured_photo` dibiarkan `null`
sampai ada endpoint unggah foto.

### POST /teacher/attendance/manual — **Ada**

```json
{ "student_id": 21, "schedule_id": 12, "status": "Izin" }
```

Boleh selama jam pelajaran dan untuk koreksi sampai akhir hari yang sama. Jam masuk asli
tidak berubah saat status dikoreksi. 422 untuk tanggal selain hari ini.

### GET /teacher/classes/{classId}/students — **Ada**

Hanya untuk kelas yang diajar guru ini.

### Nilai — **Ada**

| Metode | Path | Keterangan |
|---|---|---|
| GET | `/teacher/grades` | Filter: `class_id`, `subject_id`, `assessment_type`, `student_id`. Hanya nilai milik guru ini. |
| POST | `/teacher/grades` | `student_id`, `subject_id`, `class_id`, `assessment_type`, `title`, `score`, `teacher_notes?`, `assessment_date?`. Hanya mapel yang diajar guru di kelas itu. |
| PUT | `/teacher/grades/{id}` | `title?`, `score?`, `teacher_notes?`, `assessment_date?` |
| DELETE | `/teacher/grades/{id}` | |

Satu baris nilai = satu asesmen. Halaman Input Nilai di mobile mengirim satu request per asesmen
(Tugas, UTS, atau UAS), bukan pasangan UTS + UAS.

---

## 4. Wali murid (`role:parent`)

Semua endpoint menerima `student_id` opsional (untuk wali dengan lebih dari satu anak).
Tanpa itu, dipakai anak pertama yang tertaut.

### GET /parent/student-status — **Ubah**

Query opsional: `date`. Setiap item `schedules_today` ditambah:

```json
{
  "schedule_id": 12,
  "subject": "Matematika",
  "teacher": "Ahmad Hidayat, S.Pd.",
  "time_slot": "07:30:00 - 09:00:00",
  "status": "Belum_Absen",
  "method": null,
  "check_in_time": null,
  "teacher_status": "Digantikan",
  "substitute_teacher": "Siti Rahma, S.Pd.",
  "exception": null
}
```

### GET /parent/student-grades — **Ada**

Respons `data`: `student`, `summary` (`total_assessments`, `overall_average`), dan
`grades_by_subject[]` (`subject_name`, `average_score`, `assessments[]` berisi `id`, `type`,
`title`, `score`, `teacher_notes`, `assessment_date`, `teacher_name`). Sudah memuat Tugas, UTS, dan UAS.

### GET /parent/attendance-recap — **Baru**

Query: `month` (`YYYY-MM`).

```json
{
  "status": "success",
  "data": {
    "month": "2026-11",
    "summary": { "hadir": 40, "sakit": 2, "izin": 1, "alpa": 0 },
    "days": [
      { "date": "2026-11-23", "day": "Senin", "hadir": 4, "sakit": 0, "izin": 0, "alpa": 0, "is_holiday": false }
    ]
  }
}
```

### GET /parent/schedules — **Baru**

Jadwal mingguan kelas anak: `data` = daftar per hari, tiap jadwal berisi `subject_name`,
`teacher_name`, `start_time`, `end_time`.

---

## 5. Notifikasi (`role:teacher`, `role:parent`) — **Baru**

| Metode | Path | Keterangan |
|---|---|---|
| GET | `/notifications` | Query `page`. Urut terbaru. |
| POST | `/notifications/{id}/read` | Tandai sudah dibaca. |
| POST | `/notifications/read-all` | |

Item:

```json
{
  "id": 88,
  "type": "teacher_substituted",
  "title": "Guru pengganti hari ini",
  "body": "Matematika 07:30 diajar oleh Siti Rahma, S.Pd.",
  "data": { "schedule_id": 12, "date": "2026-11-23" },
  "read_at": null,
  "created_at": "2026-11-23T06:45:00+07:00"
}
```

Jenis awal: `teacher_substituted`, `schedule_exception`, `student_absent`, `substitute_assigned` (untuk guru pengganti).
Push notification (FCM) menyusul; aplikasi mengambil daftar ini saat dibuka dan saat ditarik untuk refresh.

---

## 6. Admin di aplikasi (`role:admin`) — **Baru**

Hanya untuk pendaftaran wajah. Pengelolaan data lainnya tetap di web admin.

### GET /admin/classes

```json
{ "status": "success", "data": [ { "id": 3, "name": "8-A", "total_students": 30, "face_enrolled": 24 } ] }
```

### GET /admin/classes/{classId}/students

Query opsional: `search` (nama atau NIS).

```json
{ "status": "success", "data": [ { "id": 21, "nis": "20260001", "full_name": "Ahmad Rizki", "face_enrolled": true, "face_enrolled_at": "2026-11-20T09:12:00+07:00" } ] }
```

### POST /admin/students/{id}/face-enrollment

Daftar atau daftar ulang (menimpa data lama).

```json
{ "model_version": "mobilefacenet-v1", "embeddings": [[0.0132, -0.2210, "..."], ["..."], ["..."]], "liveness_passed": true }
```

- `embeddings`: 3–5 sampel (lurus, kiri, kanan, ...). Panjang setiap sampel harus sama dan sesuai model.
- 201: `data` = `{ "student_id": 21, "face_enrolled": true, "samples": 3 }`.
- 422: jumlah atau panjang sampel salah, atau `liveness_passed` bukan `true`.

### DELETE /admin/students/{id}/face-enrollment

Menghapus data wajah siswa. 200.

---

## 7. Proses di server — **Baru**

| Perintah | Jadwal | Tugas |
|---|---|---|
| `attendance:close-sessions` | tiap 5 menit | Jadwal yang sudah lewat jam selesai hari ini: siswa tanpa catatan dicatat `Alpa` dengan metode `auto_close`. Jadwal dengan pengecualian dilewati. Wali menerima notifikasi `student_absent`. |

Saat development: jalankan `php artisan schedule:work` di samping `php artisan serve`.

## 8. Yang dikelola lewat web admin (bukan API mobile)

Data guru dan siswa, impor Excel, jadwal pelajaran, penunjukan guru pengganti,
pengecualian jadwal (jam kosong/libur), dan status pendaftaran wajah tiap siswa.

---

## 9. Belum diputuskan

- **Panjang embedding** (128 atau 192) dan nilai `model_version` final: ditentukan dari hasil prototipe pengenalan wajah.
- **Unggah foto bukti presensi** (`captured_photo`): perlu endpoint unggah terpisah, atau tidak disimpan sama sekali demi privasi.
- **Batas kecocokan wajah** (`confidence_score` minimal untuk dianggap cocok): ditentukan dari prototipe.
