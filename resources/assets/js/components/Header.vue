<template>
    <nav class="navbar navbar-default">
        <div class="container-fluid">

            <div class="navbar-header">
                <a class="navbar-brand" href="{{ appUrl }}">{{ appName }}</a>
            </div>

            <ul class="nav navbar-nav">

                <vs-dropdown>
                    <button class="btn btn-primary navbar-btn" data-toggle="dropdown">
                        Import groups
                        <span class="caret"></span>
                    </button>

                    <ul class="dropdown-menu">
                        <li>
                            <a @click="importGroups(false)">
                                Append new translations
                            </a>
                        </li>
                        <li>
                            <a @click="importGroups(true)">
                                Replace existing translations
                            </a>
                        </li>
                    </ul>
                </vs-dropdown>

                <li style="margin-left: 5px">
                    <button class="btn btn-default navbar-btn" href="#" @click="showFinder = true">
                        Find translations
                    </button>
                </li>

            </ul>
            <div class="navbar-form navbar-right">
                <typeahead :data="USstate" placeholder="Search in translations ::TODO"></typeahead>
            </div>
        </div>
        <translator-finder :show.sync="showFinder"></translator-finder>
    </nav>
</template>

<script>

    import typeahead from "vue-strap/src/Typeahead.vue";
    import DropdownComponent from "vue-strap/src/Dropdown.vue";

    import FinderModelComponent from "./FinderModel.vue";

    export default {
        props: ['appName', 'appUrl'],

        components: {
            typeahead,
            'vs-dropdown': DropdownComponent,
            'translator-finder': FinderModelComponent
        },

        data() {
            return {
                USstate: ['Alabama', 'Alaska', 'Arizona', 'Bbbb'],
                showFinder: false
            }
        },

        methods: {

            importGroups: function(replace) {
                this.$dispatch('main-loading-start', 'Importing groups...');

                this.$http.post('import', {replace}).then(
                        function(res) {
                            var data = res.json();

                            this.$emit('groups-imported');

                            this.$dispatch('main-loading-stop');

                            this.$dispatch('main-alert', true, {
                                title: 'Yeah!!',
                                description: data.counter + ' groups imported'
                            });

                        },
                        function(err) {
                            this.$dispatch('main-loading-stop');
                        }
                );
            }

        },

        ready() {

        }
    }
</script>