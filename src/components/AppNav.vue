<template>
  <nav
    id="nav"
    class="lg:h-36 h-24 w-screen flex justify-evenly items-start shadow-xl fixed -top-0 bg-white z-50 border-b-2 border-blue-main transition duration-300 ease-in-out"
    :class="{'h-auto':navActivated}"
  >
    <div
      class="w-9/12  my-3 h-3/12 flex lg:flex-row flex-col lg:justify-between lg:items-center"
    >
      <div @click="navActivated=false" class="">
        <router-link to="/">
          <img :src="logoURL" alt="sour screen logo" />
        </router-link>
      </div>
      <div
        class="w-8/12 m-auto flex lg:justify-evenly lg:flex-row flex-col items-center justify-center lg:h-auto h-0 overflow-hidden transition duration-300 ease-in-out"
        :class="{ 'h-full': navActivated }"

      >
        <router-link @click.native="activateNav" to="/database"><h2>Database</h2></router-link>
        <router-link @click.native="activateNav" to="/list"><h2>List</h2></router-link>
        <Search />
      </div>
    </div>
    <svg
      @click="activateNav"
      class="feather feather-menu cursor-pointer lg:hidden mt-7"
      xmlns="http://www.w3.org/2000/svg"
      width="60"
      height="60"
      viewBox="0 0 30 30"
      fill="none"
      stroke="currentColor"
      stroke-width="2"
      stroke-linecap="round"
      stroke-linejoin="round"
    >
      <line 
       class="stroke-current text-red-main"
      x1="3" y1="12" x2="21" y2="12"></line>
      <line 
       class="stroke-current text-blue-main"
      x1="3" y1="8" x2="21" y2="8"></line>
      <line 
       class="stroke-current text-blue-main"
      x1="3" y1="16" x2="21" y2="16"></line>
    </svg>
  </nav>
</template>

<script>
import Search from "@/components/Search.vue";
export default {
  components: {
    Search,
  },
  data() {
    return {
      navActivated: false,
    };
  },
  computed: {
    logoURL() {
      return `${this.$store.state.baseHostURL}/wp-content/uploads/2020/12/sour_screen_logo-animated-v3.svg`;
    },
  },
  methods: {
    activateNav() {
      this.navActivated === false ? this.navActivated = true : this.navActivated = false
    },
  },
};
</script>

<style lang="scss">

</style>