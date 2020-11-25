<template>
  <div class="post-card">
    <div>
      <img src="#" alt="">
    </div>
      <div>
        <h3>{{post.title.rendered}}</h3>
        <p v-html="post.excerpt.rendered"></p>
      </div>
  </div>
</template>

<script>
export default {
 props:{
   post:Object
 }
}
</script>

<style lang='scss'>
h3 {
  margin: 40px 0 0;
}
ul {
  list-style-type: none;
  padding: 0;
}
li {
  display: inline-block;
  margin: 0 10px;
}
a {
  color: #42b983;
}
</style>
