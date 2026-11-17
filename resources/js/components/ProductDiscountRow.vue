<template>
  <div v-if="generalRate > 0 || showMember" class="flex items-center justify-between gap-1 mt-0 text-[12px] font-medium text-gray-700">
    <span class="whitespace-nowrap" :class="{ 'line-through text-gray-500': memberActive }">
      <template v-if="generalRate > 0">Discount: <span class="font-bold" :class="memberActive ? 'text-gray-500' : 'text-red-500'">{{ generalRate }}%</span></template>
    </span>
    <span v-if="showMember" class="member-discount relative ml-auto" @mouseenter="show" @mouseleave="open = false">
      <button ref="trigger" type="button" class="whitespace-nowrap text-[12px] font-medium"
        :class="memberActive ? 'text-primary_color' : 'text-gray-500 line-through'"
        :aria-describedby="tooltipId" :aria-expanded="open" @click.stop="show" @focus="show" @blur="open = false"
        @keydown.esc="open = false">
        Member Discount: {{ memberRate }}%
      </button>
      <Teleport to="body">
      <span v-if="open" :id="tooltipId" role="tooltip" class="member-discount-tooltip" :style="position">
        Become a TORI member and keep at least Tk {{ minimumBalance }} in your membership account before the order payment is deducted to receive this discount.
      </span>
      </Teleport>
    </span>
  </div>
  <div v-else class="h-[7px]"></div>
</template>

<script setup>
import { computed, ref, getCurrentInstance, onMounted, onBeforeUnmount } from 'vue';
const props = defineProps({ product: { type: Object, required: true } });
const open = ref(false);
const trigger = ref(null);
const position = ref({});
const show = () => {
  const rect = trigger.value.getBoundingClientRect();
  const width = Math.min(250, window.innerWidth - 16);
  position.value = {
    width: `${width}px`,
    left: `${Math.max(8, Math.min(rect.right - width, window.innerWidth - width - 8))}px`,
    bottom: `${window.innerHeight - rect.top + 6}px`,
  };
  open.value = true;
};
const close = () => { open.value = false; };
onMounted(() => {
  window.addEventListener('scroll', close, true);
  window.addEventListener('resize', close);
});
onBeforeUnmount(() => {
  window.removeEventListener('scroll', close, true);
  window.removeEventListener('resize', close);
});
const tooltipId = `member-discount-${getCurrentInstance().uid}`;
const generalRate = computed(() => Number(props.product.standard_discount_percent ?? props.product.general_discount_percent ?? props.product.discount_percent ?? 0));
const memberRate = computed(() => Number(props.product.member_discount_percent || 0));
const showMember = computed(() => props.product.membership_discount_enabled === true && props.product.member_discount_visible === true);
const memberActive = computed(() => showMember.value && props.product.membership_discount_eligible === true
  && props.product.discount_source === 'member');
const minimumBalance = computed(() => Number(props.product.membership_discount_minimum_balance || 0)
  .toLocaleString('en-BD', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
</script>

<style scoped>
.member-discount-tooltip {
  position: fixed;
  pointer-events: none;
  width: 250px;
  max-width: 80vw;
  padding: 10px;
  border-radius: 6px;
  background: #263238;
  color: white;
  font-size: 12px;
  line-height: 1.5;
  font-weight: 400;
  z-index: 9999;
  box-shadow: 0 2px 8px #0003;
}
</style>
