<template>
  <div>
    <Header />

    <section class="account-area py-3">
      <div class="container">
        <div class="row">
          <!-- Sidebar Menu -->
          <div class="col-md-3 mb-3">
            <div class="account-sidebar bg-white p-3 rounded shadow-sm">
              <h5 class="font-weight-bold mb-3">My Account</h5>
              <ul class="list-unstyled account-nav">
                <li>
                  <router-link :to="{ name: 'account.dashboard' }" :class="{ active: isActive('account.dashboard') }">Dashboard</router-link>
                </li>
                <li>
                  <router-link :to="{ name: 'account.orders' }" :class="{ active: isActive('account.orders') }">Orders</router-link>
                </li>
                <li>
                  <router-link :to="{ name: 'account.profile' }" :class="{ active: isActive('account.profile') }">Profile</router-link>
                </li>
                <li>
                  <router-link :to="{ name: 'account.password' }" :class="{ active: isActive('account.password') }">Change Password</router-link>
                </li>
                <li>
                  <a href="javascript:void(0);" @click="showModal = true">Logout</a>
                </li>
              </ul>
            </div>
          </div>

          <!-- Page Content Slot -->
          <div class="col-md-9">
            <div class="account-content bg-white p-4 rounded shadow-sm">
              <router-view />
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Logout Confirmation Modal -->
    <div class="modal" tabindex="-1" v-if="showModal" style="display: block; background: rgba(0,0,0,0.5);">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Confirm Logout</h5>
            <!-- <button type="button" class="btn-close" @click="showModal = false">X</button> -->
          </div>
          <div class="modal-body">
            <p>Are you sure you want to logout?</p>
          </div>
          <div class="modal-footer">
            <button class="btn btn-secondary" @click="showModal = false">Cancel</button>
            <button class="btn btn-danger" @click="confirmLogout">Yes, Logout</button>
          </div>
        </div>
      </div>
    </div>

    <Footer />
  </div>
</template>

<script>
import Header from '@/components/Header.vue';
import Footer from '@/components/Footer.vue';
import { useToast } from 'vue-toastification';
import { mapActions } from 'vuex';

const toast = useToast();
export default {
  name: "AccountLayout",
  data() {
    return {
      showModal: false
    };
  },
  components: { Header, Footer },
  mounted(){
    if (window.innerWidth > 991) {
      $("#sp_vertical_menu .vertical_menu").insertBefore(
        "#sp_header_top .col-lg-9.text-left"
      );
    }
  },
  methods: {
    isActive(name) {
      return this.$route.name === name;
    },
    ...mapActions("auth", ["logout"]),
    async confirmLogout() {
      try {
        await this.logout();
        toast.success('Logged out successfully');
        this.$router.push("/login");
      } catch (error) {
        toast.error(error.response?.data?.message || 'Logout failed');
      } finally {
        this.showModal = false;
      }
    }
  }
};
</script>

<style scoped>
.account-nav li {
  margin-bottom: 10px;
}
.account-nav a {
  color: #495057;
  font-weight: 500;
  display: block;
  padding: 10px 15px;
  border-radius: 8px;
  transition: all 0.3s ease;
  text-decoration: none;
}
.account-nav a.active,
.account-nav a:hover {
  background-color: var(--primary-color);
  color: #fff;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}

/* Mobile Responsive: Horizontal Scrollable Tabs */
@media (max-width: 767px) {
  .account-sidebar h5 {
    display: none; /* Hide title to save space on mobile */
  }
  .account-sidebar {
    padding: 15px 10px !important;
    margin-bottom: 15px;
  }
  .account-nav {
    display: flex;
    overflow-x: auto;
    white-space: nowrap;
    padding-bottom: 5px;
    margin-bottom: 0;
    -webkit-overflow-scrolling: touch;
    gap: 10px;
  }
  .account-nav::-webkit-scrollbar {
    height: 3px;
  }
  .account-nav::-webkit-scrollbar-thumb {
    background-color: #dee2e6;
    border-radius: 10px;
  }
  .account-nav li {
    margin-bottom: 0;
  }
  .account-nav a {
    display: inline-block;
    padding: 8px 20px;
    background-color: #f8f9fa;
    border: 1px solid #e9ecef;
    font-size: 14px;
  }
  .account-nav a.active {
    background-color: var(--primary-color);
    border-color: var(--primary-color);
  }
}
</style>
