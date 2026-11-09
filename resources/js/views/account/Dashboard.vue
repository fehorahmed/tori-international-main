<template>
  <div class="account-dashboard-wrapper">
    <div v-if="isLoggedIn" class="dashboard-content">
      <div class="welcome-header">
        <h2 class="welcome-title">Welcome back, <span class="text-custom-primary">{{ user.name }}</span>! 👋</h2>
        <p class="text-muted">Manage your profile, orders, and preferences from your personal dashboard.</p>
      </div>

      <div class="row">
        <!-- Shomriddhi Advance Card -->
        <div class="col-md-6 mb-4" v-if="user.shomriddhi_account">
          <div class="card premium-card shadow-sm border-0 h-100 bg-custom-primary text-white position-relative overflow-hidden">
            <div class="bg-shape"></div>
            <div class="card-body p-4 d-flex flex-column justify-content-center position-relative z-index-1">
              <h5 class="fw-bold mb-3"><i class="fas fa-wallet me-2"></i> Shomriddhi Advance</h5>
              <div class="d-flex justify-content-between align-items-end mt-2">
                <div>
                  <span class="d-block small text-white-50 text-uppercase fw-bold mb-1">Principal Balance</span>
                  <span class="fs-2 fw-bold">৳ {{ Number(user.shomriddhi_account.principal_balance).toLocaleString() }}</span>
                </div>
                <div class="text-end">
                  <span class="d-block small text-white-50 text-uppercase fw-bold mb-1">Accrued Interest</span>
                  <span class="fs-4 fw-bold">৳ {{ Number(user.shomriddhi_account.accrued_interest || 0).toLocaleString() }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Membership Card View -->
        <div class="col-md-6 mb-4" v-if="user.cards && user.cards.length > 0">
          <div class="card premium-card shadow-sm border-0 h-100 bg-dark text-white member-card-bg position-relative overflow-hidden">
            <div class="card-body p-4 d-flex flex-column justify-content-between position-relative z-index-1">
              <div class="d-flex justify-content-between align-items-start mb-3">
                <h5 class="fw-bold"><i class="fas fa-id-card me-2"></i> Membership Card</h5>
                <i class="fas fa-wifi fa-rotate-90 text-white-50 fs-4"></i>
              </div>
              <div class="mt-4">
                <span class="d-block small text-white-50 text-uppercase fw-bold mb-1">Card Number</span>
                <span class="fs-4 fw-bold letter-spacing-1">{{ formatCardNumber(user.cards[0].card_number) }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-md-12">
          <div class="card premium-card shadow-sm border-0">
            <div class="card-header bg-white border-0 pt-4 pb-0">
              <h5 class="card-title mb-0 fw-bold"><i class="fas fa-user-circle me-2 text-custom-primary"></i> Personal Information</h5>
            </div>
            <div class="card-body">
              <div class="row info-grid">
                <div class="col-md-6 mb-3 px-2">
                  <div class="info-item h-100">
                    <span class="info-label text-muted d-block small text-uppercase fw-bold mb-1">Full Name</span>
                    <span class="info-value fs-5">{{ user.name }}</span>
                  </div>
                </div>
                <div class="col-md-6 mb-3 px-2">
                  <div class="info-item h-100">
                    <span class="info-label text-muted d-block small text-uppercase fw-bold mb-1">Email Address</span>
                    <span class="info-value fs-5">{{ user.email || 'Not Provided' }}</span>
                  </div>
                </div>
                <div class="col-md-6 mb-3 px-2">
                  <div class="info-item h-100">
                    <span class="info-label text-muted d-block small text-uppercase fw-bold mb-1">Mobile Number</span>
                    <span class="info-value fs-5">{{ user.mobile_no || 'Not Provided' }}</span>
                  </div>
                </div>
                <div class="col-md-6 mb-3 px-2">
                  <div class="info-item h-100">
                    <span class="info-label text-muted d-block small text-uppercase fw-bold mb-1">Member Number</span>
                    <span class="info-value fs-5"><span class="badge bg-custom-primary rounded-pill px-3">{{ user.member_no || 'N/A' }}</span></span>
                  </div>
                </div>
                <div class="col-md-12 mb-3 px-2">
                  <div class="info-item h-100">
                    <span class="info-label text-muted d-block small text-uppercase fw-bold mb-1">Shipping Address</span>
                    <span class="info-value fs-5">{{ user.address || 'No address provided yet. Update your profile to add one.' }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <div v-else class="text-center py-5">
      <div class="spinner-border text-custom-primary" role="status" v-if="loading">
        <span class="visually-hidden">Loading...</span>
      </div>
      <p v-else class="text-muted fs-5 mt-3">Please log in to access your account dashboard.</p>
    </div>
  </div>
</template>

<script>
import { mapState, mapGetters, mapActions } from 'vuex';

export default {
  name: "Dashboard",

  computed: {
    ...mapState('auth', ['user', 'loading']),
    ...mapGetters('auth', ['isLoggedIn']),
  },

  mounted() {
    if (!this.user) {
      this.fetchUser();
    }
  },

  methods: {
    ...mapActions('auth', ['fetchUser']),
    formatCardNumber(num) {
      if (!num) return '';
      return num.toString().match(/.{1,4}/g)?.join(' ') || num;
    }
  }
};
</script>

<style scoped>
.letter-spacing-1 {
  letter-spacing: 2px;
}
.bg-shape {
  position: absolute;
  top: -50px;
  right: -50px;
  width: 150px;
  height: 150px;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 50%;
}
.member-card-bg {
  background: linear-gradient(135deg, #2b2b2b 0%, #1a1a1a 100%);
}
.z-index-1 {
  z-index: 1;
}
.account-dashboard-wrapper {
  animation: fadeIn 0.4s ease-in-out;
}

.welcome-title {
  font-weight: 700;
  font-size: 2rem;
  color: #2c3e50;
}

.premium-card {
  border-radius: 16px;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
  overflow: hidden;
}

.premium-card:hover {
  box-shadow: 0 10px 30px rgba(0,0,0,0.08) !important;
}

.info-grid {
  padding: 10px;
}

.info-item {
  padding: 15px;
  background-color: #f8f9fa;
  border-radius: 12px;
  transition: background-color 0.2s ease;
}

.info-item:hover {
  background-color: #f1f3f5;
}

.info-value {
  color: #343a40;
  font-weight: 500;
  word-break: break-word;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>

<style scoped>
.text-custom-primary { color: var(--primary-color) !important; }
.bg-custom-primary { background-color: var(--primary-color) !important; color: #fff; }
</style>
