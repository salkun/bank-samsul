<template>
  <nav class="navbar navbar-expand-lg navbar-dark bg-success">
    <div class="container-fluid">
      <a class="navbar-brand" href="#">GreenSaver</a>
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
