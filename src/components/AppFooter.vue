<template>
  <footer
  class="flex flex-col justify-between md:flex-row bg-blue-xLight p-10">
    <div
    v-if="this.$store.state.accessToken !==''"
    >
    <h2 class="text-white">Logged in as {{username}}</h2>
    <p
    @click="logOut">Log out</p>
    </div>
    <div 
    v-else
    class="w-full md:w-2/4">
      <h2 class="text-white">Login</h2>
        <router-link to="/forms/signup">
        Or create an account
        </router-link>
      <Login/>
    </div>
    <div class="w-1/4">
      <h2 class="text-white">Privacy Policy</h2>
    </div>
  </footer>
</template>

<script>
import Login from '@/forms/Login.vue';
export default {
  components:{
    Login
  },
  data() {
    return {
      loginInfo: {
        username: "",
        password: "",
      },
    };
  },
  computed: {
    username() {
      return this.$store.state.userInfo.username;
    },
  },
  methods: {
    logOut(){
      this.$store.commit('REMOVE_USER_INFO')
    }
  },
};
</script>

<style>
</style>