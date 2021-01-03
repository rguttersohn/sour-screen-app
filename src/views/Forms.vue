<template>
  <div id="forms" class="w-screen lg:py-36 py-24 h-screen">
    <div class="w-11/2 md:w-2/4 mx-auto my-10">
      <div class="flex justify-evenly mx-auto w-full mb-0">
    <h2 class="text-white bg-red-xLight py-3 w-1/4 text-center cursor-pointer" @click="toSignUp">Sign Up</h2>
    <h2 class="text-white bg-blue-xLight py-3 w-1/4 text-center cursor-pointer" @click="toLogin">Login</h2>
    </div>
    <router-view />
    </div>
  </div>
</template>
<script>
export default {
  methods: {
    toSignUp() {
      if (this.$route.path !== "/forms/signup") {
        this.$router.push({ path: "/forms/signup" });
      }
    },
    toLogin() {
      if (this.$route.path !== "/forms/login") {
        this.$router.push({ path: "/forms/login" });
      }
    },
  },
};
</script>