<template>
  <div id="app">
    <div id="nav">
      <router-link to="/">Sour Screen logo</router-link>
      <router-link to="/database">Database</router-link>
      <router-link to="/list">List</router-link>
    </div>
    <router-view />
  </div>
</template>

<style lang="scss">
#app {
  font-family: Oswald, Helvetica, Arial, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  text-align: center;
  color: #2c3e50;
  width: 100vw;
  height: 100vh;

  #nav {
    padding: 30px;
    display: flex;
    justify-content: space-evenly;
    border-bottom:3px solid lightgray


    a {
      font-weight: 700;
      color: #2c3e50;
    }

    a.router-link-exact-active {
      color: #42b983;
    }
  }
}

h1 {
  font-family: Oswald;
  font-size: 24px;
  font-style: normal;
  font-variant: normal;
  font-weight: 700;
  line-height: 26.4px;
}
h3 {
  font-family: Oswald;
  font-size: 14px;
  font-style: normal;
  font-variant: normal;
  font-weight: 700;
  line-height: 15.4px;
}
p {
  font-family: Oswald;
  font-size: 14px;
  font-style: normal;
  font-variant: normal;
  font-weight: 400;
  line-height: 20px;
}
blockquote {
  font-family: Oswald;
  font-size: 21px;
  font-style: normal;
  font-variant: normal;
  font-weight: 400;
  line-height: 30px;
}
pre {
  font-family: Oswald;
  font-size: 13px;
  font-style: normal;
  font-variant: normal;
  font-weight: 400;
  line-height: 18.5667px;
}
a {
  font-weight: 700;
  color: #2c3e50;
}

</style>
