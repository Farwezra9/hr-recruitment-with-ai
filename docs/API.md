# REST API v1

Base URL: `http://localhost:8000/api/v1`
Format: JSON (`Accept: application/json`). Waktu memakai ISO 8601.

## Autentikasi

Ambil token, lalu kirim di header `Authorization: Bearer <token>`.

```bash
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Accept: application/json" -H "Content-Type: application/json" \
  -d '{"email":"admin@example.com","password":"PASSWORDMU","device_name":"postman"}'
```
```json
{ "token": "1|abc...", "token_type": "Bearer", "user": { "id": 1, "name": "Admin HR", "role": "admin" } }
```

Token login berlaku 7 hari (`expires_at` ada di balasan). Token integrasi buatan admin di halaman **Profil** berlaku 90 hari. Cabut token aktif: `POST /auth/logout` (204). Mengganti password mencabut semua token.

Login dikunci 5 menit setelah 5 percobaan gagal (per email + IP), balasan `429`.

## Hak akses per role

| Role | Akses |
|---|---|
| `admin` | Semua endpoint |
| `recruiter` | Semua kecuali `/users` |
| `hiring_manager` | Hanya membaca (GET) lowongan, pelamar, kandidat, tes, interview, statistik |
| `interviewer` | `/auth/me`, interview miliknya (lihat dan isi feedback), CV kandidat yang ia wawancarai |

## Konvensi

| Situasi | Kode | Isi |
|---|---|---|
| Berhasil membaca/mengubah | 200 | `{"data": ...}` |
| Berhasil membuat | 201 | `{"data": ...}` + header `Location` |
| Berhasil menghapus | 204 | kosong |
| Diterima, diproses di background (AI) | 202 | `{"status": "running"}` |
| Belum login / token salah | 401 | `{"message": "..."}` |
| Role tidak boleh | 403 | `{"message": "..."}` |
| Tidak ditemukan | 404 | `{"message": "Resource tidak ditemukan."}` |
| Konflik keadaan | 409 | `{"message": "..."}` |
| Validasi gagal | 422 | `{"message": "...", "errors": {"field": ["..."]}}` |
| Terlalu banyak request | 429 | `{"message": "..."}` |

Daftar (list) memakai paginasi: `?page=2&per_page=20` (maks 100). Balasan berisi `data`, `links`, dan `meta` (`current_page`, `last_page`, `total`, ...).

## Endpoint

### Publik (tanpa token)
| Method | Path | Keterangan |
|---|---|---|
| POST | `/auth/login` | Ambil token (maks 5/menit) |
| GET | `/public/jobs` | Lowongan berstatus `open`. Filter: `q` |
| GET | `/public/jobs/{id}` | Detail lowongan terbuka |
| POST | `/public/jobs/{id}/applications` | Kirim lamaran, `multipart/form-data` (maks 6/menit) |

Field lamaran: `name`, `email`, `phone`, `linkedin_url`, `portfolio_url`, `cv` (PDF/DOCX, maks 5 MB), `consent` (harus `1`).

```bash
curl -X POST http://localhost:8000/api/v1/public/jobs/1/applications \
  -H "Accept: application/json" \
  -F name="Budi" -F email="budi@example.com" -F consent=1 -F cv=@cv-budi.pdf
```

### Lowongan
| Method | Path | Role | Keterangan |
|---|---|---|---|
| GET | `/jobs` | staff | Filter: `status`, `q`, `trashed=only` |
| GET | `/jobs/{id}` | staff | |
| POST | `/jobs` | recruiter | 201 |
| PUT/PATCH | `/jobs/{id}` | recruiter | PATCH: kirim hanya field yang berubah |
| DELETE | `/jobs/{id}` | recruiter | 204, soft delete |
| POST | `/jobs/{id}/restore` | recruiter | Pulihkan |
| POST | `/jobs/{id}/rescreen` | recruiter | Nilai ulang semua pelamar aktif (202) |
| GET | `/jobs/{id}/applications` | staff | Pelamar per lowongan (filter sama dengan `/applications`) |

### Pelamar (applications)
| Method | Path | Role | Keterangan |
|---|---|---|---|
| GET | `/applications` | staff | Filter: `q`, `job_id`, `stage`, `status`, `recommendation`, `min_score`. Urut: `sort=-created_at` (default), `ai_score`, `-ai_score` |
| GET | `/applications/{id}` | staff | Lengkap: kandidat, lowongan, interview, tes, riwayat tahap |
| PATCH | `/applications/{id}` | recruiter | Pindah tahap: `{"stage":"shortlisted","notes":"..."}` |
| POST | `/applications/{id}/screening` | recruiter | Jalankan AI (202) |
| GET | `/applications/{id}/screening` | staff | Status `idle/running/done/failed` + hasil terakhir |

Tahap: `applied, ai_screening, shortlisted, assessment, interview, offer, hired, rejected`. Pindah ke `shortlisted/offer/hired/rejected` mengirim email ke kandidat.

### Kandidat
| Method | Path | Role | Keterangan |
|---|---|---|---|
| GET | `/candidates` | staff | Filter: `q`, `source` |
| GET | `/candidates/{id}` | staff | |
| GET | `/candidates/{id}/cv` | staff, atau interviewer terkait | Unduh file CV |

### Interview
| Method | Path | Role | Keterangan |
|---|---|---|---|
| GET | `/interviews` | semua role | Staff: semua. Lainnya: miliknya. Filter: `status`, `interviewer_id`, `upcoming=1`, `from`, `to` |
| GET | `/interviews/{id}` | staff / pemilik | |
| POST | `/applications/{id}/interviews` | recruiter | 201. Jadwal bertabrakan ditolak (422) |
| PUT/PATCH | `/interviews/{id}` | recruiter: semua field. Interviewer pemilik: `status`, `feedback`, `rating` | |
| DELETE | `/interviews/{id}` | recruiter | 204 |

### Tes (assessments)
| Method | Path | Role | Keterangan |
|---|---|---|---|
| GET | `/assessments` | staff | Filter: `is_active` |
| GET | `/assessments/{id}` | staff | Termasuk soal (kunci jawaban hanya untuk recruiter) |
| POST/PUT/PATCH/DELETE | `/assessments[/{id}]` | recruiter | DELETE: 409 kalau sudah pernah dikirim ke kandidat |
| POST | `/assessments/{id}/questions` | recruiter | `options` = array teks berurutan (label A-F otomatis) |
| PUT/PATCH/DELETE | `/questions/{id}` | recruiter | |
| GET | `/applications/{id}/assessments` | staff | Tes milik pelamar |
| POST | `/applications/{id}/assessments` | recruiter | Kirim tes: `{"assessment_id": 1}` (201) |
| GET | `/candidate-assessments/{token}` | staff | Hasil dan jawaban |
| PATCH | `/candidate-assessments/{token}/grade` | recruiter | Nilai esai: `{"essay_points":{"12":3.5}}` |

### Lainnya
| Method | Path | Role |
|---|---|---|
| GET | `/departments` | staff |
| GET | `/stats` | staff |
| GET/POST/PUT/PATCH/DELETE | `/users[/{id}]` | admin |

## Contoh alur

```bash
TOKEN="1|abc..."

# 5 pelamar dengan skor AI tertinggi untuk lowongan 1
curl "http://localhost:8000/api/v1/jobs/1/applications?sort=-ai_score&per_page=5" \
  -H "Authorization: Bearer $TOKEN" -H "Accept: application/json"

# Shortlist pelamar 12
curl -X PATCH http://localhost:8000/api/v1/applications/12 \
  -H "Authorization: Bearer $TOKEN" -H "Accept: application/json" -H "Content-Type: application/json" \
  -d '{"stage":"shortlisted"}'
```

## Catatan

- Untuk front-end di domain lain, atur origin yang diizinkan di `config/cors.php` (`allowed_origins`).
- Hasil AI (`ai.score`, `ai.analysis`) adalah alat bantu penyaringan. Jangan ditampilkan ke pelamar.
- Endpoint lama tanpa versi (`/api/jobs`, `/api/applications/...`) sudah diganti. Pakai `/api/v1/...`.
