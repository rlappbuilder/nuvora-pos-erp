Nah, ini yang akan menjadi pola standar Nuvora ERP.
Setiap membuat halaman Edit, langkahnya selalu:
Copy Create.vue.
Ganti props.
Ganti inisialisasi form.
Ganti post() → put().
Ganti save() → update().
Hapus Save & New.
Hapus previewCode().
Ubah mode="create" → mode="edit".
Ubah judul halaman.