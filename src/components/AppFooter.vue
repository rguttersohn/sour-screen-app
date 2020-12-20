<template>
<div id="footer"
class="flex flex-col justify-between md:flex-row bg-blue-xLight p-10">
    <div
    class="w-full md:w-2/4">
        <h2 class="text-white">Login to track the movies you've watched</h2>
        <p class="text-blue-main">Login to see </p>
        <form 
        class="flex flex-col lg:flex-row justify-evenly lg:justify-start items-stretch sm:h-40 md:h-10 flex-wrap"
        @submit.prevent="handleSubmission"
        >
            <input class="m-2 p-3" type="text" placeholder="User Name">
            <input class="m-2 p-3" type="password" placeholder="Password">
            <input class="m-2 p-3 cursor-pointer" type="submit" value="Login">
        </form>
    </div>
    <div
    class="w-1/4">
        <h2 class="text-white">Privacy Policy</h2>
    </div>
</div>

</template>

<script>
export default {

    methods:{
        handleSubmission(){
            console.log('submitted')
        }
    }
};
</script>

<style>
</style>