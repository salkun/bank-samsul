import Swal from 'sweetalert2'

export const swal = {
  success(message = 'Berhasil!', title = 'Sukses') {
    return Swal.fire({
      title,
      text: message,
      icon: 'success',
      timer: 2000,
      showConfirmButton: false,
      toast: true,
      position: 'top-end',
    })
  },

  error(message = 'Terjadi kesalahan!', title = 'Gagal') {
    return Swal.fire({
      title,
      text: message,
      icon: 'error',
      confirmButtonColor: '#dc2626',
    })
  },

  warning(message = 'Apakah Anda yakin?', title = 'Peringatan') {
    return Swal.fire({
      title,
      text: message,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Ya, lanjutkan',
      cancelButtonText: 'Batal',
      confirmButtonColor: '#16a34a',
      cancelButtonColor: '#d33',
    })
  },

  info(message = 'Informasi', title = 'Info') {
    return Swal.fire({
      title,
      text: message,
      icon: 'info',
      confirmButtonColor: '#16a34a',
    })
  }
}
