<template>
  <div class="w-screen lg:py-36 py-24" id="create-account">
    <div class="w-full md:w-11/12 lg:w-3/4 m-auto my-10 p-5 bg-red-xLight">
      <div v-if="serverCode !== 200">
        <h1 class="text-white">Create an Account</h1>
        <form
          @submit.prevent="handleFormSubmission"
          class="flex flex-col w-full md:w-96 m-auto"
        >
          <label for="username">Create a username</label>
          <input
            class="mb-5 p-3"
            type="text"
            id="username"
            placeholder="username"
            v-model="formData.username"
          />
          <label for="email">Add your email</label>
          <input
            class="mb-5 p-3"
            type="email"
            id="email"
            placeholder="email"
            v-model="formData.email"
            required
          />
          <label for="password">Create a password</label>
          <input
            class="mb-5 p-3"
            type="password"
            id="password"
            placeholder="password"
            v-model="formData.password"
            required
          />
          <label for="confirm-password">Confirm password</label>
          <input
            class="mb-5 p-3"
            type="password"
            id="confirm-password"
            placeholder="confirm password"
            v-model="formData.confirmPassword"
            required
          />
          <input
            class="mb-5 p-3 cursor-pointer"
            type="submit"
            value="Create Your Account"
          />
          <label v-if="formMessage !== ''">{{ formMessage }}</label>
          <label v-else>{{ serverMessage }}</label>
        </form>
      </div>
      <div v-else
      class="flex flex-col w-full md:w-96 m-auto"
      >
        <h1 class="text-white mb-5 text-center">Booyah!</h1>
        <h3 class="mb-5 text-center text-red-main">
          {{serverMessage}}
        </h3>
        <button
        class="bg-red-main hover:bg-red-light text-white font-bold py-2 px-4 rounded mb-5"
        @click="handleLogin">
        <h2 class="font-mono">Click to login!</h2>
        </button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      formData: {
        username: "",
        email: "",
        password: "",
        confirmPassword: "",
      },
      newAccountInfo: {},
      loginInfo:{},
      formMessage: "",
      user:{
        userName:"",
        userId:""
      },
    }
  },
  methods: {
    async handleFormSubmission() {
      this.formMessage = "";
      if (this.formData.confirmPassword !== this.formData.password) {
        this.formData.password = "";
        this.formData.confirmPassword = "";
        this.formMessage = "Password does not match. Rewrite password.";
      } else {
        this.newAccountInfo.username = this.formData.username;
        this.newAccountInfo.email = this.formData.email;
        this.newAccountInfo.password = this.formData.password;
        this.loginInfo.username = this.formData.username;
        this.loginInfo.password = this.formData.password;
        this.$store.dispatch("submitNewAccount", this.newAccountInfo);
        this.formData.username = "";
        this.formData.email = "";
        this.formData.password = "";
        this.formData.confirmPassword = "";
        this.newAccountInfo = [];
      }
    },
    handleLogin(){
      this.$store.dispatch('submitLogin',this.loginInfo)
    }
  },
  computed: {
    serverMessage() {
      return this.$store.state.serverMessage;
    },
    serverCode() {
      return this.$store.state.serverCode;
    },
  },
};
</script>

<style>
</style>