# Laravel File Storage

[Kembali](readme.md)

## Latar belakang topik

Laravel File Storage digunakan untuk penyimpanan file ke dalam server project laravel itu sendiri atau ke server terpisah, seperti Amazon S3, Google Drive, Dropbox, dll.

Dengan File Storage user bisa melakukan download dan upload file untuk berbagai keperluan. Penggunaan fitur ini misalnya adalah fitur upload foto untuk profil, keperluan download dokumen, dan sebagainya.

## Konsep-konsep

Laravel memiliki abstraksi filesystem dari package PHP Flysystem buatan Frank de Jorge. Flysystem Laravel menyediakan driver sederhana untuk bekerja dengan filesystem lokal, SFTP, dan Amazon S3. 

### Konfigurasi

File konfigurasi untuk filesystem laravel terdapat di `config/filesystems.php`. Dalam file, dapat dikonfigurasi "disk" filesystem. Tiap disk merepresentasikan driver storage dan lokasi storage. Contoh konfigurasi untuk tiap driver tersedia di dalam file konfigurasi sehingga kita dapat dengan mudah memodifikasi konfigurasi.

- Local Driver

Saat memakai `local` driver, semua operasi file relatif terhadap direktori `root` yang didefinisi dalam file config `filesystems`. Default adalah direktori `storage/app`. Seperti contoh, method berikut akan melakukan write ke `storage/app/example.txt`:
```
use Illuminate\Support\Facades\Storage;

Storage::disk('local')->put('example.txt', 'Contents');
```

- Public Disk

Disk `public` dalam file config `filesystems` dimaksudkan untuk file yang dapat diakses publik. Disk `public` memakai driver `local` dan menyimpan file-filenya di dalam `storage/app/public` sebagai default.

- Caching

Caching digunakan untuk meningkatkan performa. Untuk mengaktifkan caching, dapat ditambahkan `cache` dalam konfigurasi. Opsi `cache` berupa array yang berisi nama `disk`, waktu `expire` dalam hitungan detik, dan `prefix` cache:
```
's3' => [
    'driver' => 's3',

    // Other Disk Options...

    'cache' => [
        'store' => 'memcached',
        'expire' => 600,
        'prefix' => 'cache-prefix',
    ],
],
```

### Mendapatkan File
Untuk mendapatkan file, dapat digunakan berbagai macam method:

- `get`

Method `get` dapat dipakai untuk mendapatkan konten dalam file. Method ini mengembalikan konten file dalam bentuk raw string. Semua file path harus ditentukan relatif terhadap lokasi `root`:
```
$contents = Storage::get('file.jpg');
```

- `download`

Method `download` dapat digunakan untuk membuat response yang memerintahkan browser user untuk mendownload file sesuai path. Method `download` menerima nama file sebagai argument kedua, yang akan menentukan nama file yang menentukan nama file yang dilihat oleh user yang mendownload file. Array HTTP header dapat dipakai sebagai argument ketiga:
```
return Storage::download('file.jpg');

return Storage::download('file.jpg', $name, $headers);
```

- `url`

Method `url` dapat dipakai untuk mendapatkan URL suatu file. Jika kita memakai driver `local`, method ini akan menambahkan `/storage` didepan path dan mengembalikan URL relatif file. Jika kita memakai driver `s3`, semua remote URL dikembalikan:
```
use Illuminate\Support\Facades\Storage;

$url = Storage::url('file.jpg');
```

### Penyimpanan File
Untuk penyimpanan file, dapat digunakan berbagai macam method:

- `put`

Method `put` digunakan untuk menyimpan konten file ke dalam disk. PHP `resource` dapat dimasukkan ke method `put`. Semua file path harus ditentukan relatif terhadapt lokasi `root` yang dikonfigurasi:
```
use Illuminate\Support\Facades\Storage;

Storage::put('file.jpg', $contents);

Storage::put('file.jpg', $resource);
```

- `putFile` dan `putFileAs`

Method `putFile` dan `putFileAs` digunakan untuk automatic streaming suatu file. Automatic streaming meringankan beban memori:

```
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;

// Automatically generate a unique ID for filename...
$path = Storage::putFile('photos', new File('/path/to/photo'));

// Manually specify a filename...
$path = Storage::putFileAs('photos', new File('/path/to/photo'), 'photo.jpg');
```

- `prepend` dan `append`

Method `prepend` dan `append` digunakan untuk menambah tulisan di awal atau akhir file:

```
Storage::prepend('file.log', 'Prepended Text');

Storage::append('file.log', 'Appended Text');
```

- `copy` dan `move`

Method `copy` dipakai untuk menyalin suatu file ke lokasi lain, sedangkan method `move` digunakan untuk rename atau memindahkan file:
```
Storage::copy('old/file.jpg', 'new/file.jpg');

Storage::move('old/file.jpg', 'new/file.jpg');
```

- `store`

Laravel menyediakan method untuk menyimpan file dengan method `store` untuk instance file yang telah diupload. `store` dipanggil dengan path tujuan file:
```
<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserAvatarController extends Controller
{
    /**
     * Update the avatar for the user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $path = $request->file('avatar')->store('avatars');

        return $path;
    }
}
```

### Menghapus File
Untuk menghapus file, dapat digunakan method-method berikut:

- `delete`

Method `delete` menerima nama file tunggal atau file array yang akan dihapus:
```
use Illuminate\Support\Facades\Storage;

Storage::delete('file.jpg');

Storage::delete(['file.jpg', 'file2.jpg']);
```
Jika perlu, bisa ditentukan disk mana yang perlu dihapus filenya:
```
use Illuminate\Support\Facades\Storage;

Storage::disk('s3')->delete('path/file.jpg');
```

### Direktori
Beberapa method yang berhubungan dengan manajemen direktori adalah sebagai berikut:
- `files` dan `allFiles`

Method `files` mengembalikan array dari semua file yang ada dalam direktori. `allFiles` digunakan jika kita ingin melibatkan semua subdirektori di dalamnya:
```
use Illuminate\Support\Facades\Storage;

$files = Storage::files($directory);

$files = Storage::allFiles($directory);
```
- `directories` dan `allDirectories`

Method `directories` mengembalikan array dari semua direktori yang ada dalam direktori. `allDirectories` digunakan jika kita ingin melibatkan semua subdirektori di dalamnya:
```
$directories = Storage::directories($directory);

$directories = Storage::allDirectories($directory);
```
- `makeDirectory`

Method `makeDirectory` akan membuatkan suatu direktori beserta subdirektori yang diperlukan:
```
Storage::makeDirectory($directory);
```
- `deleteDirectory`

Method `deleteDirectory` akan menghapus direktori dan semua file didalamnya:
```
Storage::deleteDirectory($directory);
```

## Langkah-langkah tutorial

Untuk uji coba file storage, akan dibuat sebuah modul untuk upload file.

### Langkah pertama

Di Halaman welcome Laravel `resources/views/welcome.blade.php`, Kita ganti konten dalam welcome page menjadi upload file:
```
<div>
    <form action="upload" method="post" enctype="multipart/form-data">
        @csrf
        <input type="file" name="image" id="">
        <button type="submit">Submit</button>
    </form>
</div>
```

![](img/file-storage/SC1.png)

![](img/file-storage/1_WelcomePage.png)

### Langkah kedua

Selanjutnya, ditambahkan route ke untuk suatu controller di `routes/web.php`. Kita beri nama HomeController:
```
Route::post('upload', 'App\Http\Controllers\HomeController@upload');
```

![](img/file-storage/SC2.png)

### Langkah ketiga

Kita tambahkan controller dengan menggunakan artisan:
```
php artisan make:controller HomeController
```

### Langkah keempat

Terakhir, ditambahkan fungsi upload ke dalam controller:
```
public function upload(Request $request){
    $path = $request->file('image')->store('');
    dd($path);
}
```

![](img/file-storage/SC3.png)

### Hasil

Form dari view memiliki action `upload`, yang akan diroute ke `App\Http\Controllers\HomeController@upload`. Route akan memanggil controller HomeController dan menjalankan method upload. Method upload akan membuatkan suatu path yang digunakan untuk menyimpan file upload. `dd($path)` akan melakukan dump variabel `$path` yang akan menunjukkan nama file baru yang telah diupload.

![](img/file-storage/1_WelcomePage.png)

![](img/file-storage/2_Dump.png)

![](img/file-storage/3_Result.png)
