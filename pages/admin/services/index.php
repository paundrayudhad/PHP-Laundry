<?php
session_start();

//get data
$table = "services";
$data = $database->getReference($table)->getValue();

// Handle AJAX requests
if (isset($_POST['tambah']) && isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
  $nama_layanan = $_POST['nama_layanan'];
  $harga_layanan = $_POST['harga'];
  $table = 'services';
  $formData = [
    'nama_layanan' => $nama_layanan,
    'harga_layanan' => $harga_layanan
  ];

  $storeData = $database->getReference($table)->push($formData);

  // Return JSON response for AJAX request
  header('Content-Type: application/json');
  if ($storeData) {
    echo json_encode(['status' => 'success', 'message' => 'Data berhasil ditambah']);
  } else {
    echo json_encode(['status' => 'error', 'message' => 'Data gagal ditambah']);
  }
  exit;
}

// Handle delete requests
if (isset($_POST['delete_key']) && isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
  $deleteKey = $_POST['delete_key'];
  $table = 'services';

  $deleteData = $database->getReference($table . '/' . $deleteKey)->remove();

  header('Content-Type: application/json');
  if ($deleteData) {
    echo json_encode(['status' => 'success', 'message' => 'Data berhasil dihapus']);
  } else {
    echo json_encode(['status' => 'error', 'message' => 'Data gagal dihapus']);
  }
  exit;
}

if (isset($_POST['edit']) && isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
  $key = $_POST['edit_key'];
  $nama_layanan = $_POST['edit_nama_layanan'];
  $harga_layanan = $_POST['edit_harga'];
  $table = 'services';
  
  $updateData = [
    'nama_layanan' => $nama_layanan,
    'harga_layanan' => $harga_layanan
  ];

  $updateResult = $database->getReference($table . '/' . $key)->update($updateData);

  header('Content-Type: application/json');
  if ($updateResult) {
    echo json_encode([
      'status' => 'success', 
      'message' => 'Data berhasil diupdate',
      'key' => $key,
      'nama_layanan' => $nama_layanan,
      'harga_layanan' => $harga_layanan
    ]);
  } else {
    echo json_encode(['status' => 'error', 'message' => 'Data gagal diupdate']);
  }
  exit;
}

include 'layouts/header.php';
?>

<div class="container">
  <div class="page-inner">
    <?php
    if (isset($_SESSION['notif'])) {
      echo "<div class='alert alert-primary mt-3' role='alert'>" . $_SESSION['notif'] . "</div>";
      unset($_SESSION['notif']);
      
    }
    ?>
    <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">
      <div>
        <h3 class="fw-bold mb-3">Layanan</h3>
        <h6 class="op-7 mb-2">Kelola Layanan</h6>
      </div>
      <div class="ms-md-auto py-2 py-md-0">
        <a href="#" class="btn btn-primary btn-round" data-bs-toggle="modal" data-bs-target="#modalTambahLayanan">Tambah Layanan</a>
      </div>
    </div>
    <div class="table-responsive">
    <table class="table table-hover">
      <thead>
        <tr>
          <th scope="col">No</th>
          <th scope="col">Nama Layanan</th>
          <th scope="col">Harga Layanan/kg</th>
          <th scope="col">Action</th>
        </tr>
      </thead>
      <tbody>
        <?php
        

        if ($data > 0) {
          $i = 1;
          foreach ($data as $key => $value) {
        ?>
            <tr>
              <th scope="row"><?= $i++; ?></th>
              <td><?= $value['nama_layanan']; ?></td>
              <td><?= $value['harga_layanan']; ?></td>
              <td>
                <div class="d-flex">
                <button type="button" 
            class="btn btn-success me-2 edit-btn" 
            data-key="<?= $key; ?>"
            data-nama="<?= $value['nama_layanan']; ?>"
            data-harga="<?= $value['harga_layanan']; ?>">
      Edit
    </button>
                  <button type="button" class="btn btn-danger delete-btn" data-key="<?= $key; ?>">Delete</button>
                </div>
              </td>
            </tr>
        <?php
          }
        }
        ?>
      </tbody>
    </table>
    </div>
  </div>
</div>

<!-- Modal Tambah Layanan -->
<div class="modal fade" id="modalTambahLayanan" tabindex="-1" aria-labelledby="modalTambahLayananLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalTambahLayananLabel">Tambah Layanan</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="formTambahLayanan" method="POST">
          <div class="mb-3">
            <label for="nama_layanan" class="form-label">Nama Layanan</label>
            <input type="text" class="form-control" id="nama_layanan" name="nama_layanan" placeholder="Masukkan Nama Layanan" required>
          </div>
          <div class="mb-3">
            <label for="harga_layanan" class="form-label">Harga Layanan/kg</label>
            <input type="number" class="form-control" id="harga_layanan" name="harga" placeholder="Masukkan Harga Layanan" required>
          </div>
          <button type="submit" class="btn btn-primary" name="tambah">Simpan</button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Modal Edit Layanan -->
<div class="modal fade" id="modalEditLayanan" tabindex="-1" aria-labelledby="modalEditLayananLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalEditLayananLabel">Edit Layanan</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="formEditLayanan" method="POST">
          <input type="hidden" id="editKey" name="edit_key">
          <div class="mb-3">
            <label for="editnama_layanan" class="form-label">Nama Layanan</label>
            <input type="text" class="form-control" id="editnama_layanan" name="edit_nama_layanan" placeholder="Masukkan Nama Layanan" required>
          </div>
          <div class="mb-3">
            <label for="editharga_layanan" class="form-label">Harga Layanan/kg</label>
            <input type="number" class="form-control" id="editharga_layanan" name="edit_harga" placeholder="Masukkan Harga Layanan" required>
          </div>
          <button type="submit" class="btn btn-primary" name="edit">Simpan</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
  document.getElementById('formTambahLayanan').addEventListener('submit', function(event) {
    event.preventDefault();

    const formData = new FormData(this);
    formData.append('tambah', '1');

    fetch(window.location.href, {
        method: 'POST',
        body: formData,
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        }
      })
      .then(response => response.json())
      .then(data => {
        if (data.status === 'success') {
          // Hide modal and reset form
          const modal = bootstrap.Modal.getInstance(document.getElementById('modalTambahLayanan'));
          modal.hide();
          this.reset();

          // Show success notification using SweetAlert2
          Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: data.message,
            timer: 2000,
            showConfirmButton: false
          }).then(() => {
            location.reload();
          });
        } else {
          // Show error notification using SweetAlert2
          Swal.fire({
            icon: 'error',
            title: 'Gagal!',
            text: 'Gagal menyimpan data: ' + data.message
          });
        }
      })
      .catch(error => {
        console.error('Error:', error);
        Swal.fire({
          icon: 'error',
          title: 'Error!',
          text: 'Terjadi kesalahan sistem'
        });
      });
  });

  document.addEventListener('click', function(e) {
  if (e.target.classList.contains('edit-btn')) {
    const key = e.target.dataset.key;
    const nama_layanan = e.target.dataset.nama;
    const harga_layanan = e.target.dataset.harga;

    // Set values in the edit modal
    document.getElementById('editKey').value = key;
    document.getElementById('editnama_layanan').value = nama_layanan;
    document.getElementById('editharga_layanan').value = harga_layanan;

    // Show the edit modal
    const editModal = new bootstrap.Modal(document.getElementById('modalEditLayanan'));
    editModal.show();
  }
});

// Handle form submission for editing
document.getElementById('formEditLayanan').addEventListener('submit', function(event) {
  event.preventDefault();

  const formData = new FormData(this);
  formData.append('edit', '1');

  fetch(window.location.href, {
    method: 'POST',
    body: formData,
    headers: {
      'X-Requested-With': 'XMLHttpRequest'
    }
  })
  .then(response => response.json())
  .then(data => {
    if (data.status === 'success') {
      // Hide modal and reset form
      const modal = bootstrap.Modal.getInstance(document.getElementById('modalEditLayanan'));
      modal.hide();
      this.reset();

      // Show success notification using SweetAlert2
      Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: data.message,
        timer: 2000,
        showConfirmButton: false
      }).then(() => {
        location.reload(); // Reload to show updated data
      });
    } else {
      // Show error notification using SweetAlert2
      Swal.fire({
        icon: 'error',
        title: 'Gagal!',
        text: 'Gagal menyimpan data: ' + data.message
      });
    }
  })

  .catch(error => {
    console.error('Error:', error);
    Swal.fire({
      icon: 'error',
      title: 'Error!',
      text: 'Terjadi kesalahan sistem'
    });
  });
});

  document.addEventListener('click', function(e) {
    if (e.target.classList.contains('delete-btn')) {
      // Show delete confirmation using SweetAlert2
      Swal.fire({
        title: 'Apakah Anda yakin?',
        text: "Data yang dihapus tidak dapat dikembalikan!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, hapus!',
        cancelButtonText: 'Batal'
      }).then((result) => {
        if (result.isConfirmed) {
          const key = e.target.dataset.key;
          const formData = new FormData();
          formData.append('delete_key', key);

          fetch(window.location.href, {
              method: 'POST',
              body: formData,
              headers: {
                'X-Requested-With': 'XMLHttpRequest'
              }
            })
            .then(response => response.json())
            .then(data => {
              if (data.status === 'success') {
                // Remove the row from the table
                e.target.closest('tr').remove();

                // Show success notification using SweetAlert2
                Swal.fire({
                  icon: 'success',
                  title: 'Berhasil!',
                  text: data.message,
                  timer: 2000,
                  showConfirmButton: false
                });
              } else {
                // Show error notification using SweetAlert2
                Swal.fire({
                  icon: 'error',
                  title: 'Gagal!',
                  text: 'Gagal menghapus data: ' + data.message
                });
              }
            })
            .catch(error => {
              console.error('Error:', error);
              Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'Terjadi kesalahan sistem'
              });
            });
        }
      });
    }

    if (e.target.classList.contains('edit-btn')) {
      const key = e.target.dataset.key;
      const nama_layanan = e.target.dataset.nama;
      const harga_layanan = e.target.dataset.harga;

      // Set values in the edit modal
      document.getElementById('editKey').value = key;
      document.getElementById('editnama_layanan').value = nama_layanan;
      document.getElementById('editharga_layanan').value = harga_layanan;

      // Show the edit modal
      const editModal = new bootstrap.Modal(document.getElementById('modalEditLayanan'));
      editModal.show();
    }
  });
</script>

<?php include('layouts/footer.php'); ?>