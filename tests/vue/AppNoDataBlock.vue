<template>

    <!-- This file is to run the tests -->

</template>


<script>
import { mapWritableState } from "pinia";

export default {

    components: {},
    async beforeMount() {


    },

    unmounted() {
    },


    computed: {
        ...mapWritableState(useHomepageStore, ['config', 'is_loading', 'homepage', 'main']),



    },

    methods: {

    }

}
</script>
