<template>
  <div class="search-container" @click="revealSearch">
    <svg
      xmlns="http://www.w3.org/2000/svg"
      width="24"
      height="24"
      viewBox="0 0 24 24"
      fill="none"
      stroke-width="2"
      stroke-linecap="round"
      stroke-linejoin="round"
      class="feather feather-search"
    >
      <circle cx="11" cy="11" r="8"></circle>
      <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
    </svg>
    <h3>click to search</h3>
    <div class="search-results" :class="{'search-active':reveal}">
      <ul>
        <li></li>
      </ul>
    </div>
  </div>
</template>

<script>
import { mapState } from "vuex";
export default {
data(){
    return{
reveal:false
    }
},
  computed: {
    ...mapState({
      posts: (state) => state.posts,
    }),
  },
  methods:{
      revealSearch(){
          if (this.reveal){
              this.reveal = false
          } else {
              this.reveal = true;
          }
      }
  }
};
</script>

<style lang="scss">
$color-red: #ff3333;
$color-blue: #0099cc;
$color-lightred: #ffe7ff;
$color-lightblue: #b1bbed;

.search-container {
  display: flex;
  align-items: center;
  justify-content: space-between;
  cursor: pointer;
  width: 300px;

  h3 {
    color: lightgray;
    transition: color 0.3s ease-in-out;
    width: 200px;
  }
  svg {
    stroke: lightgray;
    width: 50%;
    transition: stroke 0.3s ease-in-out;
  }

  &:hover > h3,
  &:hover > svg {
    stroke: $color-red;
    color: $color-red;
  }

  .search-results{
      max-height:0;
      overflow:hidden;
  }
  .search-active{
      max-height:100%;
  }
}
</style>