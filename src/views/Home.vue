<template>
  <div class="home-container">
    <section id="home-banner-container">
      <HomeBanner />
    </section>
    <section id="why-bad-movies-section">
      <h1>Why Bad Movies?</h1>
      <div class="why-bad-movies-container">
        <div class="flex-wrapper">
          <img
            src="https://www.api-sourscreen.com/wp-content/uploads/2020/12/pumpkin-head.svg"
            alt=""
          />
          <p>
            bad movies are great because Lorem ipsum dolor sit amet consectetur
            adipisicing elit. Delectus corporis hic inventore quae possimus
            incidunt velit aspernatur cumque deleniti necessitatibus, blanditiis
            tempora reiciendis maiores saepe repudiandae porro nobis earum
            voluptatibus.
          </p>
        </div>
      </div>
    </section>
    <section id="new-movies-section">
      <div class="new-movies-container">
        <h1>Latest additions to our database:</h1>
        <div class="home-card-flex-wrapper">
          <div
            class="home-card-container"
            v-for="movie in newMovies"
            :key="movie.id"
          >
            <HomeCard :post="movie" />
          </div>
        </div>
      </div>
    </section>
    <section id="new-lists-section">
      <div class="new-lists-container">
        <h1>Our latest lists:</h1>
        <div class="home-card-flex-wrapper">
          <div
            class="home-card-container"
            v-for="list in newLists"
            :key="list.id"
          >
            <HomeCard :post="list" />
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script>
import { mapGetters } from "vuex";
import HomeCard from "@/components/HomeCard.vue";
import HomeBanner from "@/components/HomeBanner.vue";
export default {
  components: {
    HomeCard,
    HomeBanner,
  },
  computed: mapGetters(["newMovies", "newLists"]),
};
</script>

<style lang="scss">
$color-red: #ff3333;
$color-blue: #0099cc;
$color-lightred: #ffe7ff;
$color-lightblue: #b1bbed;
$color-red-background: #fdf2fd;
$color-blue-background: #f1f3fd;

.home-container {
  width: 100vw;

  section {
    margin: 3% auto;
  }

  #home-banner-container {
    width: 100%;
    margin: auto;
  }

  #why-bad-movies-section {
    width: 100%;

    .why-bad-movies-container {
      width:75%;
      margin:3% auto;
      
      h1 {
        text-align: right;
      }

      .flex-wrapper {
        margin: auto;
        width: 50%;
        display: flex;
        align-items: center;
        justify-content: space-around;

        img {
          width: 30%;
        }
        p {
          width: 50%;
        }
      }
    }
  }

  #new-movies-section {
    width: 100%;
    background-color: $color-red-background;
    padding: 1% 0;
    .new-movies-container {
      width: 75%;
      margin: 3% auto;
      h1 {
        text-align: left;
        color: $color-blue;
      }
    }
  }
  #new-lists-section {
    width: 100%;
    padding: 1% 0;
    .new-lists-container {
      width: 75%;
      margin: 3% auto;
      h1 {
        text-align: right;
        color: $color-red;
      }
    }
  }
  .home-card-flex-wrapper {
    display: flex;
    justify-content: space-evenly;
    width: 100%;
    margin: auto;
  }
}
</style>
