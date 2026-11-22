<template>
  <div>
    <Header />

    <div id="Registration_page" class="Registration-page animate__animated animate__fadeInUp">
      <div class="sp_header bg-white p-3">
        <div class="container custom_container">
          <div class="row">
            <div class="col-12">
              <ul>
                <li class="d-inline-block font-weight-bolder">
                  <router-link :to="{ name: 'home' }">home</router-link>
                </li>
                <li
                  class="d-inline-block hr_ font-weight-bolder"
                  :class="{ active: $route.name === 'register' }"
                >
                  <router-link :to="{ name: 'register' }">Registration</router-link>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>

      <div class="container custom_container">
        <h3 class="text-center">Create your account</h3>
        <div id="Registration" class="page-content card card-block p-3 p-sm-4">
          <form @submit.prevent="handleRegister" novalidate class="space-y-4">
            <p>
              Already have an account?
              <router-link :to="{ name: 'login' }"> Log in instead!</router-link>
            </p>

            <!-- Name -->
            <div>
              <label class="block font-semibold mb-1">Name*</label>
              <input
                v-model="user.name"
                type="text"
                class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-1 focus:ring-blue-500"
                :class="{'border-red-500': (submitted && !user.name) || errors.name}"
              />
              <p v-if="submitted && !user.name" class="text-red-500 text-sm mt-1">
                Please enter your name.
              </p>
              <p v-if="errors.name" class="text-red-500 text-sm mt-1">{{ errors.name[0] }}</p>
            </div>

            <!-- Mobile -->
            <div>
              <label class="block font-semibold mb-1">Mobile No*</label>
              <input
                v-model="user.mobile"
                type="tel"
                inputmode="numeric"
                autocomplete="tel-national"
                placeholder="01712345678"
                class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-1 focus:ring-blue-500"
                :class="{'border-red-500': (submitted && !user.mobile) || errors.mobile}"
              />
              <p v-if="submitted && !user.mobile" class="text-red-500 text-sm mt-1">
                Please enter your mobile number.
              </p>
              <p v-if="errors.mobile" class="text-red-500 text-sm mt-1">{{ errors.mobile[0] }}</p>
            </div>

            <!-- Email -->
            <div>
              <label class="block font-semibold mb-1">Email address (Optional)</label>
              <input
                v-model="user.email"
                type="email"
                class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-1 focus:ring-blue-500"
                :class="{'border-red-500': errors.email}"
              />
              <p v-if="errors.email" class="text-red-500 text-sm mt-1">{{ errors.email[0] }}</p>
            </div>

            <!-- Password -->
            <div>
              <label class="block font-semibold mb-1">Password*</label>
              <input
                v-model="user.password"
                type="password"
                class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-1 focus:ring-blue-500"
                :class="{'border-red-500': (submitted && !user.password) || errors.password}"
              />
              <p v-if="submitted && !user.password" class="text-red-500 text-sm mt-1">
                Please enter your password.
              </p>
              <p v-if="errors.password" class="text-red-500 text-sm mt-1">{{ errors.password[0] }}</p>
            </div>

            <div class="row mt-3">
              <div class="col-12 mb-3">
                <label>Branch (Optional)</label>
                <select v-model="user.branch_id" class="form-control"><option value="">Default branch</option><option v-for="branch in branches" :key="branch.id" :value="branch.id">{{ branch.code }} - {{ branch.name }}</option></select>
                <p v-if="errors.branch_id" class="text-danger small mt-1">{{ errors.branch_id[0] }}</p>
              </div>
              <div class="col-sm-6 mb-3"><label>Referrer type{{ user.referral_code.trim() ? "*" : " (Optional)" }}</label><select v-model="user.referral_type" class="form-control"><option value="">Select Member / Agent</option><option value="member">Member</option><option value="agent">Agent</option></select><p v-if="errors.referral_type" class="text-danger small mt-1">{{ errors.referral_type[0] }}</p></div>
              <div class="col-sm-6 mb-3"><label>Referral code (Optional)</label><input v-model="user.referral_code" class="form-control" /><p v-if="errors.referral_code" class="text-danger small mt-1">{{ errors.referral_code[0] }}</p></div>
              <div v-if="referrerName" class="col-12 mb-3 small text-success" role="status">আপনি <strong>{{ referrerName }}</strong>-এর referral-এ registration করছেন।</div>
              <div v-for="field in addressFields" :key="field.key" class="col-sm-6 mb-3">
                <label>{{ field.label }}{{ ["area", "village_id"].includes(field.key) ? " (Optional)" : "*" }}</label>
                <select v-model="user[field.key]" class="form-control" :disabled="!field.enabled" @change="addressChanged(field.key)">
                  <option value="">Select {{ field.label }}</option>
                  <option v-for="option in field.options" :key="option.value || option.id" :value="option.value || option.id">{{ option.name }}</option>
                </select>
                <p v-if="addressError(field.key)" class="text-danger small mt-1">{{ addressError(field.key) }}</p>
              </div>
              <div class="col-12 mb-3"><label>Address details (Optional)</label><textarea v-model="user.address" maxlength="500" class="form-control" rows="2"></textarea><p v-if="errors.address" class="text-danger small mt-1">{{ errors.address[0] }}</p></div>
            </div>
            <p v-if="errors.general" class="text-danger small">{{ errors.general[0] }}</p>

            <!-- Agree -->
            <div class="flex items-center">
              <input
                v-model="user.agree"
                type="checkbox"
                id="agree"
                class="mr-2 w-5 h-5 border-2 border-gray-400 rounded !accent-primary_color focus:ring-2 focus:ring-blue-300"
              />
              <label for="agree" class="text-sm mt-2">
                I agree to the
                <router-link :to="{ name: 'dynamic_pages', params: { slug: 'terms-and-conditions' } }" target="_blank" rel="noopener">Terms &amp; Conditions</router-link>
                and the
                <router-link :to="{ name: 'dynamic_pages', params: { slug: 'privacy-policy' } }" target="_blank" rel="noopener">Privacy Policy</router-link>
              </label>
            </div>
            <p v-if="submitted && !user.agree" class="text-red-500 text-sm">
              You must agree before submitting.
            </p>

            <p v-if="errors.agree" class="text-danger small">{{ errors.agree[0] }}</p>

            <!-- Buttons -->
            <div class="flex justify-between mt-3">
              <button
                id="clearButton"
                type="reset"
                class="bg-gray-300 px-4 py-2 rounded hover:bg-gray-400"
                @click="resetForm"
              >
                Reset
              </button>
              <button
                type="submit"
                class="bg-primary_color text-white px-4 py-2 rounded disabled:opacity-50"
                :disabled="isSubmitting"
              >
                {{ isSubmitting ? "Registering..." : "Save" }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <Footer />
  </div>
</template>

<script>
import axios from "@/axios";
import { readReferral, saveReferral, clearReferral } from "../utils/referralLink";
import { mapActions } from "vuex";
import Header from "../components/Header.vue";
import Footer from "../components/Footer.vue";

export default {
  name: "RegisterPage",
  data() {
    return {
      user: {
        name: "",
        mobile: "",
        email: "",
        password: "",
        agree: false,
        branch_id: "", referral_type: "", referral_code: "",
        division_id: "", district_id: "", upazila_id: "", area: "", village_id: "", address: "",
      },
      branches: [], divisions: [], districts: [], upazilas: [], areas: [], villages: [],
      referrerName: '', referralTimer: null, referralVersion: 0,
      locationVersion: 0,
      submitted: false,
      isSubmitting: false,
      errors: {}, 
    };
  },
  watch: {
    'user.referral_type': 'referralChanged',
    'user.referral_code': 'referralChanged',
  },
  beforeUnmount() { clearTimeout(this.referralTimer); ++this.referralVersion; },
  computed: {
    addressFields() {
      return [
        { key: 'division_id', label: 'Division', options: this.divisions, enabled: true },
        { key: 'district_id', label: 'District', options: this.districts, enabled: !!this.user.division_id },
        { key: 'upazila_id', label: 'Upazila', options: this.upazilas, enabled: !!this.user.district_id },
        { key: 'area', label: 'Pourashava / Union', options: this.areas, enabled: !!this.user.upazila_id },
        { key: 'village_id', label: 'Village / Road', options: this.villages, enabled: this.user.area.startsWith('union_') },
      ];
    },
  },
  async mounted() {
    const referral = readReferral();
    if (referral) { this.user.referral_type = referral.type; this.user.referral_code = referral.code; }
    if (Object.hasOwn(this.$route.query, 'ref')) {
      if (typeof this.$route.query.ref === 'string') {
        this.user.referral_code = this.$route.query.ref;
        this.user.referral_type = ['member', 'agent'].includes(this.$route.query.type) ? this.$route.query.type : '';
      }
      const query = { ...this.$route.query }; delete query.ref; delete query.type;
      this.$router.replace({ path: this.$route.path, query });
    }
    try {
      const { data } = await axios.get('/api/registration-options');
      this.branches = data.data.branches;
      this.divisions = data.data.divisions;
    } catch { this.errors = { general: ['Unable to load registration options. Please reload.'] }; }
  },
  methods: {
    ...mapActions("auth", ["register"]),

    referralChanged() {
      clearTimeout(this.referralTimer);
      const version = ++this.referralVersion;
      this.referrerName = '';
      delete this.errors.referral_code;
      delete this.errors.referral_type;
      const type = this.user.referral_type, code = this.user.referral_code.trim();
      saveReferral(type, code);
      if (code && !type) this.errors.referral_type = ['Please select Member or Agent.'];
      if (!type || !code) return;
      this.referralTimer = setTimeout(async () => {
        try {
          const { data } = await axios.get('/api/registration-referrer', { params: { type, code } });
          if (version === this.referralVersion) this.referrerName = data.name;
        } catch (error) {
          if (version === this.referralVersion) this.errors.referral_code = error.response?.data?.errors?.referral_code || ['Unable to verify referral. Please check the code.'];
        }
      }, 400);
    },
    addressError(key) {
      if (key === 'area') return this.errors.union_id?.[0] || this.errors.pourashava_id?.[0];
      return this.errors[key]?.[0];
    },
    async addressChanged(key) {
      const keys = ['division_id', 'district_id', 'upazila_id', 'area', 'village_id'];
      const lists = ['divisions', 'districts', 'upazilas', 'areas', 'villages'];
      const index = keys.indexOf(key);
      const version = ++this.locationVersion;
      for (let i = index + 1; i < keys.length; i++) { this.user[keys[i]] = ''; this[lists[i]] = []; }
      if (index >= 4 || !this.user[key]) return;
      if (key === 'area' && !this.user.area.startsWith('union_')) return;
      try {
        const { data } = await axios.get('/api/registration-locations', { params: {
          level: lists[index + 1], parent_id: key === 'area' ? this.user.area.split('_')[1] : this.user[key],
        } });
        if (version === this.locationVersion) this[lists[index + 1]] = data.data;
      } catch { if (version === this.locationVersion) this.errors = { general: ['Unable to load address options. Please select again.'] }; }
    },
    resetForm() {
      ++this.locationVersion;
      this.districts = []; this.upazilas = []; this.areas = []; this.villages = [];
      this.submitted = false;
      this.errors = {};
      this.user = {
        name: "",
        mobile: "",
        email: "",
        password: "",
        agree: false,
        branch_id: "", referral_type: "", referral_code: "",
        division_id: "", district_id: "", upazila_id: "", area: "", village_id: "", address: "",
      };
    },

    async handleRegister() {
      this.submitted = true;
      this.errors = {};

      // Frontend validation
      if (!this.user.name || !this.user.mobile || !this.user.password || !this.user.agree) {
        return;
      }

      if (!/^01[3-9][0-9]{8}$/.test(this.user.mobile)) {
        this.errors.mobile = ['Enter a valid 11-digit Bangladesh mobile number (e.g. 01712345678), without commas or other characters.'];
      }

      for (const key of ['division_id', 'district_id', 'upazila_id']) {
        if (!this.user[key]) this.errors[key] = ['Please select ' + key.replace('_id', '') + '.'];
      }
      if (Object.keys(this.errors).length) return;

      this.isSubmitting = true;

      try {
        const payload = { ...this.user, union_id: null, pourashava_id: null };
        if (payload.area) {
          const [type, id] = payload.area.split('_');
          payload[type === 'union' ? 'union_id' : 'pourashava_id'] = Number(id);
        }
        delete payload.area;
        await this.register(payload);
        clearReferral();
        this.$router.push({ name: "account.dashboard" });
      } catch (err) {
        if (err.response?.status === 422) {
          // Server validation errors
          this.errors = err.response.data.errors;
        } else {
          this.errors = { general: [err.response?.data?.message || "Registration failed."] };
        }
      } finally {
        this.isSubmitting = false;
      }
    },
  },
  components: {
    Header,
    Footer,
  },
};
</script>
