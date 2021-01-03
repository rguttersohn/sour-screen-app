<template>
  <div id="forms-page-sign-up">
    <form @submit.prevent="handleLogin">
      <label for="username">User Name</label>
      <input type="text" placeholder="User Name" v-model="loginInfo.username" />
      <label for="username">User Name</label>
      <input
        type="password"
        placeholder="Password"
        v-model="loginInfo.password"
      />
      <input class="cursor-pointer" type="submit" value="Login" />
    </form>
  </div>
</template>

<script>
export default {
  data() {
    return {
      loginInfo: {
        username: "",
        password: "",
      },
    };
  },
  methods: {
    handleLogin() {
      this.$store.dispatch("submitLogin", this.loginInfo);
    },
  },
};
</script>

<style>
footer #forms-page-sign-up {
  @apply sm:h-40 md:h-10 flex-wrap;
}

footer #forms-page-sign-up form{
  @apply flex flex-col lg:flex-row justify-evenly lg:justify-start items-stretch;
}
#forms #forms-page-sign-up {
  @apply w-full md:w-11/12 lg:w-3/4 m-auto p-5 bg-blue-xLight;
}

footer #forms-page-sign-up form input,
#forms #forms-page-sign-up form input {
  @apply mb-5 p-3;
}

#forms #forms-page-sign-up form {
  @apply flex flex-col w-full md:w-96 m-auto;
}
</style>