<template>
 <section v-if="visible.length" id="special_offer_campaigns" class="container custom_container animate__animated animate__fadeInUp mt-4 mb-2" aria-label="Special offer campaigns">
  <div class="row">
   <div class="col-12 text-center">
    <h3 class="text-[20px] md:text-[38px] leading-[20px] md:leading-[50px] text-[#39404a] block mb-0">Special Offers</h3>
    <p class="text-[13px] md:text-[18px]">Discover Limited-Time Offers on Your Favorite Products</p>
    <router-link :to="{name:'special_offers'}" class="text-primary_color text-sm font-semibold">View all offers</router-link>
   </div>
  </div>
  <div class="grid grid-cols-1 md:grid-cols-2 gap-4 my-6">
   <router-link v-for="banner in visible" :key="banner.id" :to="{name:'special_offers',params:{slug:banner.slug}}" class="block overflow-hidden rounded-lg border border-gray-200 hover:shadow-lg transition-shadow">
    <img :src="banner.image_url" :alt="banner.name" class="w-full h-auto" loading="lazy" />
   </router-link>
  </div>
 </section>
</template>
<script>
import axios from 'axios';
export default {
 data:()=>({banners:[],now:Date.now(),timer:null,lastFetch:0,disposed:false}),
 computed:{visible(){return this.banners.filter(b=>new Date(b.ends_at).getTime()>this.now);}},
 mounted(){this.load();this.timer=setInterval(()=>{this.now=Date.now();if(this.now-this.lastFetch>=30000)this.load();},1000);},
 beforeUnmount(){this.disposed=true;clearInterval(this.timer);},
 methods:{async load(){this.lastFetch=Date.now();try{const {data}=await axios.get('/api/special-offer-banners');if(!this.disposed)this.banners=data.banners;}catch(e){/* Existing page content remains available if banners cannot refresh. */}}}
};
</script>
