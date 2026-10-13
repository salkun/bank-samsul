<template>
  <nav class="navbar navbar-expand-lg navbar-dark bg-success">
    <div class="container-fluid">
      <a class="navbar-brand d-flex align-items-center gap-2" href="#">
        <img src="/images/logo.jpeg" alt="Logo" style="height: 30px; width: 30px; object-fit: contain; border-radius: 6px; background: #fff; padding: 2px;" />
        <span>Bank Samsul</span>
      </a>
      <div class="collapse navbar-collapse">
        <ul class="navbar-nav me-auto">
          <li class="nav-item"><router-link class="nav-link" to="/">Dashboard</router-link></li>
          <li class="nav-item"><router-link class="nav-link" to="/nasabah">Nasabah</router-link></li>
          <li class="nav-item"><router-link class="nav-link" to="/transaksi">Transaksi</router-link></li>
          <li class="nav-item"><router-link class="nav-link" to="/laporan">Laporan</router-link></li>
        </ul>
      </div>
    </div>
  </nav>
</template>
