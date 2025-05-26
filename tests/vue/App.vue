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

    data() {
    return {
        hpm: {
            name: "Homepage-Struktur changed",
            type: "homepage",
            component: "App",
            is_active: {
                value: true,
                name: "Aktiv",
                options_type: {
                    type: "boolean"
                },
                options: [
                    {
                        option: "ein",
                        value: true
                    },
                    {
                        option: "aus",
                        value: false
                    }
                ]
            },
            elements: {
                header: {
                    is_active: {
                        value: false,
                        name: "Kopfbereich",
                        options_type: {
                            type: "boolean"
                        },
                        options: [
                            {
                                option: "ein",
                                value: true
                            },
                            {
                                option: "aus",
                                value: false
                            }
                        ]
                    }
                },
                main: {
                    is_active: {
                        value: true,
                        name: "Hauptbereich",
                        options_type: {
                            type: "boolean"
                        },
                        options: [
                            {
                                option: "ein",
                                value: true
                            },
                            {
                                option: "aus",
                                value: false
                            }
                        ]
                    }
                },
                footer: {
                    is_active: {
                        value: false,
                        name: "Hauptbereich",
                        options_type: {
                            type: "boolean"
                        },
                        options: [
                            {
                                option: "ein",
                                value: true
                            },
                            {
                                option: "aus",
                                value: false
                            }
                        ]
                    }
                }
            }
        },
        homepageStore: null
    };
},

    computed: {
        ...mapWritableState(useHomepageStore, ['config', 'is_loading', 'homepage', 'main']),



    },

    methods: {

    }

}
</script>
