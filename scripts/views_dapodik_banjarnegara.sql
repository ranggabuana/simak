-- =================================================================================
-- SCRIPT SQL VIEW DAPODIK KHUSUS KABUPATEN BANJARNEGARA (KODE: 030400)
-- Jalankan di SQL Server Management Studio (SSMS) Server Kominfo
-- Pembaruan: Mengecualikan Perguruan Tinggi (Politeknik / STIE / STIMIK) & Dosen
-- =================================================================================

USE Datamart;
GO

-- 1. VIEW SATUAN PENDIDIKAN (SEKOLAH) KABUPATEN BANJARNEGARA
CREATE OR ALTER VIEW dbo.v_simak_sekolah_bna AS
WITH SekolahUnik AS (
    SELECT 
        s.sekolah_id,
        s.semester_id,
        LTRIM(RTRIM(s.npsn)) AS npsn,
        LTRIM(RTRIM(s.nama)) AS nama_sekolah,
        CASE 
            WHEN s.bentuk_pendidikan IN ('TK', 'KB', 'TPA', 'SPS', 'PAUD') THEN 'PAUD'
            WHEN s.bentuk_pendidikan IN ('RA', 'BA') THEN 'TK'
            WHEN s.bentuk_pendidikan IN ('SD', 'SPK SD', 'SDLB', 'SLB') THEN 'SD'
            WHEN s.bentuk_pendidikan = 'MI' THEN 'MI'
            WHEN s.bentuk_pendidikan IN ('SMP', 'SPK SMP', 'SMPLB') THEN 'SMP'
            WHEN s.bentuk_pendidikan = 'MTs' THEN 'MTS'
            WHEN s.bentuk_pendidikan IN ('SMA', 'SPK SMA', 'SMALB') THEN 'SMA'
            WHEN s.bentuk_pendidikan = 'SMK' THEN 'SMK'
            WHEN s.bentuk_pendidikan IN ('MA', 'MAK') THEN 'MA'
            WHEN s.bentuk_pendidikan IN ('PKBM', 'SKB') THEN 'PKBM'
            ELSE NULL -- Kecualikan Perguruan Tinggi (Politeknik, STIE, STIMIK, Akademi, Universitas)
        END AS jenjang,
        CASE 
            WHEN s.status_sekolah_id = 1 OR s.status_sekolah LIKE '%Negeri%' THEN 'Negeri'
            ELSE 'Swasta'
        END AS status,
        '3304' + RIGHT(LTRIM(RTRIM(s.kode_kecamatan)), 2) AS kode_kecamatan_simak,
        REPLACE(LTRIM(RTRIM(s.kecamatan)), 'Kec. ', '') AS nama_kecamatan,
        ISNULL(s.alamat_jalan, 'Banjarnegara') 
            + CASE WHEN s.rt IS NOT NULL AND s.rt <> '0' THEN ' RT ' + CAST(s.rt AS VARCHAR(10)) ELSE '' END
            + CASE WHEN s.rw IS NOT NULL AND s.rw <> '0' THEN ' RW ' + CAST(s.rw AS VARCHAR(10)) ELSE '' END AS alamat,
        s.nomor_telepon AS telepon,
        TRY_CAST(s.lintang AS DECIMAL(10,7)) AS latitude,
        TRY_CAST(s.bujur AS DECIMAL(10,7)) AS longitude,
        ROW_NUMBER() OVER (PARTITION BY s.npsn ORDER BY s.semester_id DESC, s.last_update_sekolah DESC) AS rn
    FROM datamart.sekolah s
    WHERE LTRIM(RTRIM(s.kode_kabupaten)) = '030400'
      AND (s.soft_delete_sekolah = 0 OR s.soft_delete_sekolah IS NULL)
      AND s.npsn IS NOT NULL
      AND s.nama NOT LIKE 'Politeknik%'
      AND s.nama NOT LIKE 'Sekolah Tinggi%'
      AND s.nama NOT LIKE 'STIMIK%'
      AND s.nama NOT LIKE 'STIE%'
      AND s.nama NOT LIKE 'Universitas%'
),
AkreditasiTerakhir AS (
    SELECT 
        sekolah_id,
        CASE 
            WHEN akreditasi IN ('A', 'B', 'C') THEN akreditasi
            ELSE 'Belum Terakreditasi'
        END AS akreditasi,
        ROW_NUMBER() OVER (PARTITION BY sekolah_id ORDER BY tahun DESC, tanggal_sk DESC) AS rn
    FROM dbo.akreditasi
    WHERE soft_delete = 0 OR soft_delete IS NULL
),
Kepsek AS (
    SELECT 
        sekolah_id,
        nama AS kepala_sekolah,
        ROW_NUMBER() OVER (PARTITION BY sekolah_id ORDER BY semester_id DESC) AS rn
    FROM datamart.ptk
    WHERE LTRIM(RTRIM(kode_kabupaten)) = '030400'
      AND (soft_delete_ptk = 0 OR soft_delete_ptk IS NULL)
      AND (tugas_tambahan_jabatan_ptk LIKE '%Kepala Sekolah%' OR jenis_ptk LIKE '%Kepala Sekolah%')
)
SELECT 
    su.sekolah_id,
    su.npsn,
    su.nama_sekolah,
    su.jenjang,
    su.status,
    ISNULL(ak.akreditasi, 'Belum Terakreditasi') AS akreditasi,
    su.kode_kecamatan_simak,
    su.nama_kecamatan,
    su.alamat,
    ISNULL(ks.kepala_sekolah, '-') AS kepala_sekolah,
    su.telepon,
    su.latitude,
    su.longitude
FROM SekolahUnik su
LEFT JOIN AkreditasiTerakhir ak ON su.sekolah_id = ak.sekolah_id AND ak.rn = 1
LEFT JOIN Kepsek ks ON su.sekolah_id = ks.sekolah_id AND ks.rn = 1
WHERE su.rn = 1
  AND su.jenjang IS NOT NULL;
GO


-- 2. VIEW REKAP PARTISIPASI SISWA & USIA (APK/APM/APS) KABUPATEN BANJARNEGARA
CREATE OR ALTER VIEW dbo.v_simak_siswa_bna AS
WITH SiswaUnik AS (
    SELECT 
        pd.peserta_didik_id,
        pd.sekolah_id,
        pd.semester_id,
        pd.jenis_kelamin,
        DATEDIFF(YEAR, pd.tanggal_lahir, CAST(LEFT(CAST(pd.semester_id AS VARCHAR(6)), 4) + '-07-01' AS DATE)) AS usia,
        ROW_NUMBER() OVER (PARTITION BY pd.peserta_didik_id, LEFT(CAST(pd.semester_id AS VARCHAR(6)), 4) ORDER BY pd.semester_id DESC) AS rn
    FROM datamart.peserta_didik pd
    WHERE LTRIM(RTRIM(pd.kode_kabupaten)) = '030400'
      AND (pd.soft_delete_peserta_didik = 0 OR pd.soft_delete_peserta_didik IS NULL)
      AND (pd.soft_delete_registrasi_peserta_didik = 0 OR pd.soft_delete_registrasi_peserta_didik IS NULL)
      AND pd.jenis_keluar_id IS NULL
),
RombelSekolah AS (
    SELECT 
        rb.sekolah_id,
        LEFT(CAST(rb.semester_id AS VARCHAR(6)), 4) AS tahun_semester,
        COUNT(DISTINCT rb.rombongan_belajar_id) AS jumlah_rombel
    FROM datamart.rombongan_belajar rb
    INNER JOIN dbo.v_simak_sekolah_bna s ON rb.sekolah_id = s.sekolah_id
    WHERE (rb.soft_delete = 0 OR rb.soft_delete IS NULL)
      AND rb.jenis_rombel = 1
    GROUP BY rb.sekolah_id, LEFT(CAST(rb.semester_id AS VARCHAR(6)), 4)
)
SELECT 
    s.npsn,
    s.nama_sekolah,
    s.jenjang,
    s.kode_kecamatan_simak,
    LEFT(CAST(u.semester_id AS VARCHAR(6)), 4) + '/' + CAST(CAST(LEFT(CAST(u.semester_id AS VARCHAR(6)), 4) AS INT) + 1 AS VARCHAR(4)) AS tahun_ajaran,
    CAST(LEFT(CAST(u.semester_id AS VARCHAR(6)), 4) AS INT) AS tahun,
    COUNT(u.peserta_didik_id) AS total_siswa_semua_usia,
    SUM(CASE WHEN u.jenis_kelamin = 'L' THEN 1 ELSE 0 END) AS siswa_l,
    SUM(CASE WHEN u.jenis_kelamin = 'P' THEN 1 ELSE 0 END) AS siswa_p,
    SUM(CASE WHEN u.usia BETWEEN 5 AND 6 THEN 1 ELSE 0 END) AS siswa_usia_5_6,
    SUM(CASE WHEN u.usia BETWEEN 7 AND 12 THEN 1 ELSE 0 END) AS siswa_usia_7_12,
    SUM(CASE WHEN u.usia BETWEEN 13 AND 15 THEN 1 ELSE 0 END) AS siswa_usia_13_15,
    SUM(CASE WHEN u.usia BETWEEN 16 AND 18 THEN 1 ELSE 0 END) AS siswa_usia_16_18,
    SUM(CASE 
        WHEN s.jenjang IN ('PAUD', 'TK') AND u.usia BETWEEN 5 AND 6 THEN 1
        WHEN s.jenjang IN ('SD', 'MI') AND u.usia BETWEEN 7 AND 12 THEN 1
        WHEN s.jenjang IN ('SMP', 'MTS') AND u.usia BETWEEN 13 AND 15 THEN 1
        WHEN s.jenjang IN ('SMA', 'SMK', 'MA', 'PKBM') AND u.usia BETWEEN 16 AND 18 THEN 1
        ELSE 0 
    END) AS siswa_usia_sesuai,
    SUM(CASE 
        WHEN u.jenis_kelamin = 'L' AND (
            (s.jenjang IN ('PAUD', 'TK') AND u.usia BETWEEN 5 AND 6) OR
            (s.jenjang IN ('SD', 'MI') AND u.usia BETWEEN 7 AND 12) OR
            (s.jenjang IN ('SMP', 'MTS') AND u.usia BETWEEN 13 AND 15) OR
            (s.jenjang IN ('SMA', 'SMK', 'MA', 'PKBM') AND u.usia BETWEEN 16 AND 18)
        ) THEN 1 ELSE 0 
    END) AS siswa_usia_sesuai_l,
    SUM(CASE 
        WHEN u.jenis_kelamin = 'P' AND (
            (s.jenjang IN ('PAUD', 'TK') AND u.usia BETWEEN 5 AND 6) OR
            (s.jenjang IN ('SD', 'MI') AND u.usia BETWEEN 7 AND 12) OR
            (s.jenjang IN ('SMP', 'MTS') AND u.usia BETWEEN 13 AND 15) OR
            (s.jenjang IN ('SMA', 'SMK', 'MA', 'PKBM') AND u.usia BETWEEN 16 AND 18)
        ) THEN 1 ELSE 0 
    END) AS siswa_usia_sesuai_p,
    ISNULL(MAX(r.jumlah_rombel), 1) AS jumlah_rombel
FROM SiswaUnik u
INNER JOIN dbo.v_simak_sekolah_bna s ON u.sekolah_id = s.sekolah_id
LEFT JOIN RombelSekolah r ON u.sekolah_id = r.sekolah_id AND LEFT(CAST(u.semester_id AS VARCHAR(6)), 4) = r.tahun_semester
WHERE u.rn = 1
GROUP BY 
    s.npsn, s.nama_sekolah, s.jenjang, s.kode_kecamatan_simak,
    LEFT(CAST(u.semester_id AS VARCHAR(6)), 4);
GO


-- 3. VIEW GURU & TENAGA PENDIDIK (PTK) KABUPATEN BANJARNEGARA
CREATE OR ALTER VIEW dbo.v_simak_guru_bna AS
WITH GuruUnik AS (
    SELECT 
        p.ptk_id,
        p.sekolah_id,
        LTRIM(RTRIM(p.nama)) AS nama,
        LTRIM(RTRIM(p.nip)) AS nip,
        LTRIM(RTRIM(p.nuptk)) AS nuptk,
        CASE WHEN p.jenis_kelamin IN ('L', 'P') THEN p.jenis_kelamin ELSE 'L' END AS jenis_kelamin,
        CASE 
            WHEN p.status_kepegawaian LIKE '%PNS%' THEN 'PNS'
            WHEN p.status_kepegawaian LIKE '%PPPK%' THEN 'PPPK'
            WHEN p.status_kepegawaian LIKE '%GTY%' OR p.status_kepegawaian LIKE '%Yayasan%' THEN 'GTY'
            WHEN p.status_kepegawaian LIKE '%GTT%' OR p.status_kepegawaian LIKE '%Honor Daerah%' THEN 'GTT'
            ELSE 'Honor'
        END AS status_kepegawaian,
        CASE 
            WHEN p.riwayat_sertifikasi_id IS NOT NULL OR p.nrg IS NOT NULL THEN 1 
            ELSE 0 
        END AS sudah_sertifikasi,
        ISNULL(p.riwayat_sertifikasi_bidang_studi, ISNULL(p.jenis_ptk, 'Guru Kelas/Mapel')) AS bidang_studi,
        ROW_NUMBER() OVER (PARTITION BY p.ptk_id ORDER BY p.semester_id DESC, p.last_update_ptk DESC) AS rn
    FROM datamart.ptk p
    WHERE LTRIM(RTRIM(p.kode_kabupaten)) = '030400'
      AND (p.soft_delete_ptk = 0 OR p.soft_delete_ptk IS NULL)
      AND (p.soft_delete_ptk_terdaftar = 0 OR p.soft_delete_ptk_terdaftar IS NULL)
      AND p.jenis_keluar_id IS NULL
      AND p.jenis_ptk NOT LIKE '%Dosen%'
)
SELECT 
    g.ptk_id,
    s.npsn,
    s.nama_sekolah,
    s.kode_kecamatan_simak,
    g.nama,
    g.nip,
    g.nuptk,
    g.jenis_kelamin,
    g.status_kepegawaian,
    g.sudah_sertifikasi,
    g.bidang_studi
FROM GuruUnik g
INNER JOIN dbo.v_simak_sekolah_bna s ON g.sekolah_id = s.sekolah_id
WHERE g.rn = 1;
GO

