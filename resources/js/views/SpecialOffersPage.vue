<template>
 <Header />
 <main class="container custom_container py-4">
  <h1 class="text-2xl font-bold mb-3">Special Offers</h1>
  <p class="mb-3">Limited-time offers on products and packages. Your best eligible price is applied automatically.</p>
  <div class="mb-4 flex flex-wrap gap-2">
   <router-link class="btn btn-outline-primary" :to="{name:'special_offers'}">All offers</router-link>
   <router-link v-for="c in campaigns" :key="c.id" class="btn btn-outline-primary" :to="{name:'special_offers',params:{slug:c.slug}}">{{ c.name }}</router-link>
  </div>
  <div v-if="error" role="alert" class="alert alert-danger">{{ error }} <button @click="load">Retry</button></div>
  <p v-if="loading">Loading offers…</p>
  <p v-else-if="!products.length && !error">No active offers at the moment.</p>
  <div class="grid gap-3 [grid-template-columns:repeat(auto-fill,minmax(min(100%,250px),1fr))]">
   <div v-for="p in products" :key="p.id">
    <ProductCardSingle :product="p" />
    <p v-if="p.special_offer" class="text-sm py-2">{{ p.special_offer.campaign_name }} · {{ remaining(p.special_offer.ends_at) }}</p>
   </div>
  </div>
  <div v-if="lastPage > 1" class="flex gap-3 justify-center py-4">
   <button :disabled="page <= 1 || loading" class="btn btn-outline-primary" @click="page--; load()">Previous</button>
   <span>{{ page }} / {{ lastPage }}</span>
   <button :disabled="page >= lastPage || loading" class="btn btn-outline-primary" @click="page++; load()">Next</button>
  </div>
 </main>
 <Footer />
</template>
<script>
import Header from '../components/Header.vue';
import Footer from '../components/Footer.vue';
import ProductCardSingle from '../components/ProductCardSingle.vue';
import axios from 'axios';
export default {
 components: {Header,Footer,ProductCardSingle},
 data:()=>({campaigns:[],products:[],loading:false,error:'',page:1,lastPage:1,clock:Date.now(),timer:null,sequence:0,retryAfter:0}),
 watch:{'$route.params.slug'(){this.page=1;this.load();}},
 mounted(){this.load();this.timer=setInterval(()=>{this.clock=Date.now();if((this.products.some(p=>p.special_offer && new Date(p.special_offer.ends_at).getTime()<=this.clock) || this.campaigns.some(c=>new Date(c.ends_at).getTime()<=this.clock)) && !this.loading && this.clock>=this.retryAfter)this.load();},1000);},
 beforeUnmount(){clearInterval(this.timer);this.sequence++;},
 methods:{
  async load(){const sequence=++this.sequence;this.loading=true;this.error='';try{
   const {data}=await axios.get('/api/special-offers',{params:{campaign:this.$route.params.slug,page:this.page}});
   if(sequence!==this.sequence)return;this.campaigns=data.campaigns;this.products=data.products.data;this.lastPage=data.products.last_page;
  }catch(e){if(sequence===this.sequence){this.error='Unable to load offers. Please retry.';this.retryAfter=Date.now()+15000;}}finally{if(sequence===this.sequence)this.loading=false;}},
  remaining(end){const seconds=Math.max(0,Math.floor((new Date(end).getTime()-this.clock)/1000));if(!seconds)return 'Offer ended';const days=Math.floor(seconds/86400);const h=Math.floor(seconds%86400/3600);const m=Math.floor(seconds%3600/60);return `${days ? days+'d ' : ''}${h}h ${m}m ${seconds%60}s left`;}
 }
};
</script>
