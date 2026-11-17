<template>
  <router-link v-if="hasSpecialOffers" :to="{ name: 'special_offers' }"
    class="special-offer-shortcut" aria-label="Explore Special Offers">
    <span class="offer-spark offer-spark-one" aria-hidden="true">✦</span>
    <span class="offer-spark offer-spark-two" aria-hidden="true">✧</span>
    <i class="fas fa-gift offer-gift" aria-hidden="true"></i>
    <span class="offer-shortcut-label">Special <span>Offers</span></span>
    <span class="offer-arrow" aria-hidden="true">↗</span>
  </router-link>
  <!-- Fixed Cart Box -->
  <div class="hidden [@media(min-width:992px)]:block fixed bottom-56 right-0 z-50">
    <div
      class="bg-white !w-20 shadow-lg !border overflow-hidden !border-primary_color rounded-md items-center cursor-pointer hover:shadow-xl transition"
      @click="openDrawer"
    >
      <div class="text-center gap-2 py-2 bg-primary_color">
        <i class="fas fa-shopping-basket text-xl text-white"></i><br/>
        <!-- Format totalItems to 2 digits -->
        <span class="text-sm font-medium text-white">
          {{ totalCartItem.toLocaleString('en-US', { minimumIntegerDigits: 2, useGrouping: false }) }} Items
        </span>
      </div>

      <div class="cart-price mt-1 py-1 text-center text-primary_color">
        ৳{{ cartTotal }}
      </div>
    </div>
  </div>

  <!-- DrawerCart Component -->
  <DrawerCart ref="drawer" />
</template>

<script>
import { mapGetters } from "vuex";
import DrawerCart from "./DrawerCart.vue";

export default {
  components: { DrawerCart },
  props: { hasSpecialOffers: { type: Boolean, default: false } },
  data() {
    return {
      drawer: null,
    };
  },
  computed: {
    ...mapGetters("cart",["totalCartItem", "cartTotal"]),
  },
  mounted() {
    this.drawer = this.$refs.drawer;
  },
  methods: {
    openDrawer() {
      if (this.drawer) this.drawer.open();
    },
  },
};
</script>

<style scoped>
.special-offer-shortcut {
  position: fixed;
  right: 0;
  bottom: 365px;
  z-index: 45;
  width: 80px;
  min-height: 98px;
  padding: 17px 5px 10px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 5px;
  overflow: hidden;
  border: 1px solid #ffffffb3;
  border-right: 0;
  border-radius: 16px 0 0 16px;
  background: linear-gradient(135deg, #7c3aed, #db2777 65%, #f59e0b);
  box-shadow: 0 8px 24px #c026d34d;
  color: white !important;
  text-decoration: none;
  transition: transform .2s, box-shadow .2s;
}
.special-offer-shortcut::before {
  content: '';
  position: absolute;
  inset: -50%;
  background: linear-gradient(110deg, transparent 40%, #ffffff5c 50%, transparent 60%);
  transform: translateX(-100%);
  animation: offer-shimmer 4s ease-in-out infinite;
  pointer-events: none;
}
.special-offer-shortcut:hover { transform: translateX(-3px); box-shadow: 0 10px 30px #c026d370; }
.special-offer-shortcut:focus-visible { outline: 3px solid #fbbf24; outline-offset: 3px; }
.offer-gift { font-size: 24px; filter: drop-shadow(0 2px 3px #0002); }
.offer-shortcut-label { font-size: 12px; font-weight: 700; line-height: 1.25; text-align: center; }
.offer-shortcut-label span { display: block; }
.offer-spark { position: absolute; color: #fde68a; }
.offer-spark-one { top: 7px; left: 11px; font-size: 13px; }
.offer-spark-two { top: 14px; right: 8px; font-size: 17px; }
.offer-arrow { position: absolute; bottom: 5px; right: 8px; font-size: 12px; opacity: .8; }
@keyframes offer-shimmer { 0%, 30% { transform: translateX(-100%); } 70%, 100% { transform: translateX(100%); } }
@media (max-width: 991px) {
  .special-offer-shortcut { right: 12px; bottom: 88px; width: auto; min-height: 48px; padding: 10px 15px; border: 1px solid #ffffffb3; border-radius: 30px; flex-direction: row; gap: 8px; }
  .offer-gift { font-size: 19px; }
  .offer-shortcut-label span { display: inline; }
  .offer-spark-one { top: 2px; left: 8px; font-size: 9px; }
  .offer-spark-two, .offer-arrow { display: none; }
}
@media (prefers-reduced-motion: reduce) {
  .special-offer-shortcut { transition: none; }
  .special-offer-shortcut::before { animation: none; display: none; }
}

.cart-box {
  width: 100px;
  padding: 0.75rem;
}

.cart-box-top i {
  /* Optionally adjust icon size */
}
</style>
