<?php
session_start();

//get data
$table_services = "services";
$services_data = $database->getReference($table_services)->getValue();

// Transaksi Table
$table_transactions = "transactions";
$transactions_data = $database->getReference($table_transactions)->getValue();

if (isset($_POST['tambah_transaksi']) && isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
  $service_id = $_POST['service_id'];
  $quantity = $_POST['quantity'];
  $nama_penerima = $_POST['nama_penerima']; // Nama Penerima
  $address = $_POST['address']; // Alamat
  $table = 'transactions';

  // Get service price from services data
  $service = $database->getReference($table_services . '/' . $service_id)->getValue();
  $price = $service['harga_layanan'];
  $total = $quantity * $price;

  $transactionData = [
    'service_id' => $service_id,
    'quantity' => $quantity,
    'total' => $total,
    'status' => 'pending',
    'created_at' => time(),
    'nama_penerima' => $nama_penerima,  // Menyimpan Nama Penerima
    'address' => $address                // Menyimpan Alamat
  ];

  $storeData = $database->getReference($table)->push($transactionData);

  // Return JSON response for AJAX request
  header('Content-Type: application/json');
  if ($storeData) {
    echo json_encode(['status' => 'success', 'message' => 'Transaksi berhasil ditambahkan']);
  } else {
    echo json_encode(['status' => 'error', 'message' => 'Transaksi gagal ditambahkan']);
  }
  exit;
}

// Handle delete requests for transactions
if (isset($_POST['delete_transaksi_key']) && isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
  $deleteKey = $_POST['delete_transaksi_key'];
  $table = 'transactions';

  $deleteData = $database->getReference($table . '/' . $deleteKey)->remove();

  header('Content-Type: application/json');
  if ($deleteData) {
    echo json_encode(['status' => 'success', 'message' => 'Transaksi berhasil dihapus']);
  } else {
    echo json_encode(['status' => 'error', 'message' => 'Transaksi gagal dihapus']);
  }
  exit;
}
// Handle Edit Transaction Request
if (isset($_POST['edit_transaksi']) && isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
  $transactionKey = $_POST['edit_transaksi_key'];
  $service_id = $_POST['service_id'];
  $quantity = $_POST['quantity'];
  $nama_penerima = $_POST['nama_penerima'];
  $address = $_POST['address'];

  // Get service price from services data
  $service = $database->getReference($table_services . '/' . $service_id)->getValue();
  $price = $service['harga_layanan'];
  $total = $quantity * $price;

  // Prepare updated transaction data
  $transactionData = [
    'service_id' => $service_id,
    'quantity' => $quantity,
    'total' => $total,
    'status' => 'pending',
    'nama_penerima' => $nama_penerima,
    'address' => $address,
    'updated_at' => time()
  ];

  // Update the transaction in Firebase
  $updateData = $database->getReference($table_transactions . '/' . $transactionKey)->update($transactionData);

  // Return JSON response
  header('Content-Type: application/json');
  if ($updateData) {
    echo json_encode(['status' => 'success', 'message' => 'Transaksi berhasil diperbarui']);
  } else {
    echo json_encode(['status' => 'error', 'message' => 'Transaksi gagal diperbarui']);
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
        <h3 class="fw-bold mb-3">Transaksi</h3>
        <h6 class="op-7 mb-2">Kelola Transaksi Layanan</h6>
      </div>
      <div class="ms-md-auto py-2 py-md-0">
        <a href="#" class="btn btn-primary btn-round" data-bs-toggle="modal" data-bs-target="#modalTambahTransaksi">Tambah Transaksi</a>
      </div>
    </div>
    <div class="table-responsive">
      <table class="table table-hover">
        <thead>
          <tr>
            <th scope="col">No</th>
            <th scope="col">Nama Layanan</th>
            <th scope="col">Harga Layanan/kg</th>
            <th scope="col">Jumlah</th>
            <th scope="col">Total</th>
            <th scope="col">Nama Penerima</th> <!-- Kolom Nama Penerima -->
            <th scope="col">Alamat</th> <!-- Kolom Alamat -->
            <th scope="col">Status</th>
            <th scope="col span">Action</th>
          </tr>
        </thead>
        <tbody>
          <?php
          if ($transactions_data > 0) {
            $i = 1;
            foreach ($transactions_data as $key => $transaction) {
              $service = $database->getReference($table_services . '/' . $transaction['service_id'])->getValue();
          ?>
              <tr>
                <th scope="row"><?= $i++; ?></th>
                <td><?= $service['nama_layanan']; ?></td>
                <td>Rp <?= number_format($service['harga_layanan']); ?></td>
                <td><?= $transaction['quantity']; ?></td>
                <td>Rp <?= number_format($transaction['total']); ?></td>
                <!-- Menampilkan Nama Penerima dan Alamat -->
                <td><?= isset($transaction['nama_penerima']) ? $transaction['nama_penerima'] : 'N/A'; ?></td>
                <td><?= isset($transaction['address']) ? $transaction['address'] : 'N/A'; ?></td>
                <td><div class="badge badge-warning"><?= ucfirst($transaction['status']); ?></div></td>
                <td>
                  <div class="d-flex">
                    <!-- Edit Button -->
                    <button type="button" class="btn btn-warning edit-transaksi-btn" data-key="<?= $key; ?>" data-service-id="<?= $transaction['service_id']; ?>" data-quantity="<?= $transaction['quantity']; ?>" data-recipient-name="<?= $transaction['nama_penerima']; ?>" data-address="<?= $transaction['address']; ?>">Edit</button>
                    <!-- Delete Button -->
                    <button type="button" class="btn btn-danger delete-transaksi-btn" data-key="<?= $key; ?>">Delete</button>
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

<!-- Modal Edit Transaksi -->
<div class="modal fade" id="modalEditTransaksi" tabindex="-1" aria-labelledby="modalEditTransaksiLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalEditTransaksiLabel">Edit Transaksi</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="formEditTransaksi" method="POST">
          <input type="hidden" name="edit_transaksi_key" id="editTransaksiKey">
          <div class="mb-3">
            <label for="editServiceId" class="form-label">Pilih Layanan</label>
            <select class="form-control" id="editServiceId" name="service_id" required>
              <option value="">Pilih Salah Satu</option>
              <?php foreach ($services_data as $key => $service): ?>
                <option value="<?= $key; ?>"><?= $service['nama_layanan']; ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label for="editQuantity" class="form-label">Jumlah</label>
            <input type="number" class="form-control" id="editQuantity" name="quantity" placeholder="Masukkan Jumlah" required>
          </div>
          <div class="mb-3">
            <label for="editRecipientName" class="form-label">Nama Penerima</label>
            <input type="text" class="form-control" id="editRecipientName" name="nama_penerima" placeholder="Masukkan Nama Penerima" required>
          </div>
          <div class="mb-3">
            <label for="editAddress" class="form-label">Alamat</label>
            <textarea class="form-control" id="editAddress" name="address" placeholder="Masukkan Alamat" required></textarea>
          </div>
          <div class="mb-3">
            <label for="" class="form-label">Edit Status</label>
            <select class="form-control" name="editStatus" id=editStatus"">
              <option value="pending">Pending</option>
              <option value="proses">Proses</option>
              <option value="selesai">Selesai</option>
            </select>
          </div>
          <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        </form>
      </div>
    </div>
  </div>
</div>


<!-- Modal Tambah Transaksi -->
<div class="modal fade" id="modalTambahTransaksi" tabindex="-1" aria-labelledby="modalTambahTransaksiLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalTambahTransaksiLabel">Tambah Transaksi</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="formTambahTransaksi" method="POST">
          <div class="mb-3">
            <label for="serviceId" class="form-label">Pilih Layanan</label>
            <select class="form-control" id="serviceId" name="service_id" required>
              <option value="">Pilih Salah Satu</option>
              <?php foreach ($services_data as $key => $service): ?>
                <option value="<?= $key; ?>"><?= $service['nama_layanan']; ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label for="quantity" class="form-label">Jumlah</label>
            <input type="number" class="form-control" id="quantity" name="quantity" placeholder="Masukkan Jumlah" required>
          </div>
          <!-- New fields for recipient name and address -->
          <div class="mb-3">
            <label for="recipientName" class="form-label">Nama Penerima</label>
            <input type="text" class="form-control" id="recipientName" name="nama_penerima" placeholder="Masukkan Nama Penerima" required>
          </div>
          <div class="mb-3">
            <label for="address" class="form-label">Alamat</label>
            <textarea class="form-control" id="address" name="address" placeholder="Masukkan Alamat" required></textarea>
          </div>
          <button type="submit" class="btn btn-primary" name="tambah_transaksi">Simpan</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
  document.getElementById('formTambahTransaksi').addEventListener('submit', function(event) {
    event.preventDefault();

    const formData = new FormData(this);
    formData.append('tambah_transaksi', '1');

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
          const modal = bootstrap.Modal.getInstance(document.getElementById('modalTambahTransaksi'));
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
    if (e.target.classList.contains('delete-transaksi-btn')) {
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
          const deleteKey = e.target.dataset.key;
          fetch(window.location.href, {
              method: 'POST',
              headers: {
                'X-Requested-With': 'XMLHttpRequest'
              },
              body: new URLSearchParams({
                'delete_transaksi_key': deleteKey
              })
            })
            .then(response => response.json())
            .then(data => {
              if (data.status === 'success') {
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
  });
  // Handle Edit Button Click
  document.addEventListener('click', function(e) {
    if (e.target.classList.contains('edit-transaksi-btn')) {
      // Get the transaction data from the button attributes
      const key = e.target.dataset.key;
      const serviceId = e.target.dataset.serviceId;
      const quantity = e.target.dataset.quantity;
      const recipientName = e.target.dataset.recipientName;
      const address = e.target.dataset.address;

      // Set the values in the modal
      document.getElementById('editTransaksiKey').value = key;
      document.getElementById('editServiceId').value = serviceId;
      document.getElementById('editQuantity').value = quantity;
      document.getElementById('editRecipientName').value = recipientName;
      document.getElementById('editAddress').value = address;

      // Show the modal
      const modal = new bootstrap.Modal(document.getElementById('modalEditTransaksi'));
      modal.show();
    }
  });

  // Handle form submission for editing transaction
  document.getElementById('formEditTransaksi').addEventListener('submit', function(event) {
    event.preventDefault();

    const formData = new FormData(this);
    formData.append('edit_transaksi', '1');

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
          const modal = bootstrap.Modal.getInstance(document.getElementById('modalEditTransaksi'));
          modal.hide();
          this.reset();

          // Show success notification
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
          // Show error notification
          Swal.fire({
            icon: 'error',
            title: 'Gagal!',
            text: 'Gagal menyimpan perubahan: ' + data.message
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
</script>

<?php
include 'layouts/footer.php';
?>