<template>
  <div class="home-banner">
    <div class="home-banner-content-container">
      <h1>Our Ultimate List of Bad Movies</h1>
      <h3>
        Looking to dip your toes into the waters of bad movies?<br />Check out
        our ultimate list of movies sure to make you chuckle.
      </h3>
      <router-link class="home-banner-button" :to="{ name: 'Post', params: { id: 23 } }">
        <img class="get-started-icon-container" :src="getStartedIcon" alt="icon for getting started">
      </router-link>
    </div>
  </div>
</template>

<script>
export default {
  computed:{
    getStartedIcon(){
      return `${this.$store.state.baseHostURL}wp-content/uploads/2020/12/get-started-icon.svg`
    }
  }
};
</script>

<style lang="scss">
$color-red: #ff3333;
$color-blue: #0099cc;
$color-lightred: #ffe7ff;
$color-lightblue: #b1bbed;

.home-banner {
  background: url(https://www.api-sourscreen.com/wp-content/uploads/2020/11/the-room.jpg);
  width: 100vw;
  height: 30rem;
  background-repeat: no-repeat;
  background-size: cover;
  background-attachment: fixed;
  margin: auto;
  translate:scale(1.1,1.1);

  .home-banner-content-container {
    position: relative;
    width: 75%;
    height: 100%;
    margin: 1% auto;
  }
  a,
  h1,
  h3 {
    position: absolute;
    display: block;
    margin: 3% 0;
  }
  h1,
  h3 {
    color: white;
    text-align: left;
  }
  h1 {
    top: 10%;
  }
  h3 {
    top: 40%;
  }
  a.home-banner-button {
    background-color: $color-red;
    color: white;
    padding: 3%;
    border-radius: 15px;
    text-decoration: none;
    transition: background-color 0.3s ease-in-out;
    top: 70%;
    text-transform: uppercase;

    .get-started-icon-container{
      width:10rem;
    }
  }

  a:hover {
    background-color: $color-blue;
  }
}
</style>