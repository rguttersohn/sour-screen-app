<template>
      <div class="w-screen lg:pt-36 pt-24">

<h2>Hi, {{userName}}</h2>
    </div>
</template>

<script>
    export default {
     computed:{
         userName(){
             return this.$store.state.userInfo.username
         }
     }   
    }
</script>

<style scoped>

</style>