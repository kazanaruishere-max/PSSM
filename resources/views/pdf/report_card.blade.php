<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Hasil Belajar (Rapor)</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; line-height: 1.4; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h2 { margin: 0; text-transform: uppercase; }
        .student-info { margin-bottom: 20px; }
        .student-info table { width: 100%; border-collapse: collapse; }
        .student-info td { padding: 3px 0; }
        .grades-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .grades-table th, .grades-table td { border: 1px solid #000; padding: 8px; text-align: left; }
        .grades-table th { background-color: #f2f2f2; }
        .footer { margin-top: 50px; }
        .footer table { width: 100%; }
        .footer .sign-box { text-align: center; width: 30%; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Laporan Hasil Belajar Siswa (Rapor)</h2>
        <p>Sekolah Menengah Atas PSSM Smart School</p>
    </div>

    <div class="student-info">
        <table>
            <tr>
                <td width="20%">Nama Siswa</td>
                <td width="5%">:</td>
                <td>{{ $student->name }}</td>
                <td width="20%">Kelas</td>
                <td width="5%">:</td>
                <td>{{ $class->name }}</td>
            </tr>
            <tr>
                <td>NIS</td>
                <td>:</td>
                <td>{{ $student->studentProfile->student_id_number ?? '-' }}</td>
                <td>Tahun Ajaran</td>
                <td>:</td>
                <td>{{ $class->academicYear->name }}</td>
            </tr>
        </table>
    </div>

    <h3>A. Nilai Pengetahuan & Keterampilan</h3>
    <table class="grades-table">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th>Mata Pelajaran</th>
                <th width="15%">Rata-rata Nilai</th>
                <th width="30%">Predikat & Catatan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($grades as $index => $grade)
            <tr>
                <td align="center">{{ $index + 1 }}</td>
                <td>{{ $grade['subject_name'] }}</td>
                <td align="center">{{ $grade['average_score'] }}</td>
                <td>{{ $grade['predicate'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <table>
            <tr>
                <td class="sign-box">
                    Orang Tua/Wali
                    <br><br><br><br>
                    ( ........................ )
                </td>
                <td></td>
                <td class="sign-box">
                    Jakarta, {{ date('d F Y') }}<br>
                    Wali Kelas
                    <br><br><br><br>
                    <strong>{{ $class->homeroomTeacher->name ?? '........................' }}</strong>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
