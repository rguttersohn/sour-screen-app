<template>
  <footer class="flex flex-col justify-between md:flex-row bg-blue-xLight p-10">
    <div class="w-full md:w-2/4">
      <h2 class="text-white">Login</h2>
      <p class="text-blue-main">Track the movies you've watched</p>
      <router-link :to="{ name: 'CreateAccount' }"
        >Or create an account</router-link
      >
      <form
        class="flex flex-col lg:flex-row justify-evenly lg:justify-start items-stretch sm:h-40 md:h-10 flex-wrap"
        @submit.prevent="handleLogin"
      >
        <input 
        class="m-2 p-2" 
        type="text" 
        placeholder="User Name"
        v-model="loginInfo.username"/>
        <input 
        class="m-2 p-2" 
        type="password" 
        placeholder="Password" 
        v-model="loginInfo.password" />
        <input class="m-2 p-3 cursor-pointer" type="submit" value="Login" />
      </form>
    </div>
    <div class="w-1/4">
      <h2 class="text-white">Privacy Policy</h2>
    </div>
  </footer>
</template>

<script>
export default {
    data(){
        return{
        loginInfo:{
            username:"",
            password:""
        }
    }
    },
  methods: {
    handleLogin() {
      fetch(`${this.$store.state.baseHostURL}wp-json/jwt-auth/v1/token`, {
        method: "POST",
        body: JSON.stringify(this.loginInfo),
    headers: {
        'Content-Type': 'application/json'
    }
      })
        .then((resp) => resp.json())
        .then((data) => console.log(data));
    },
  },
};
</script>

<style>
</style>