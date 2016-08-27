<template>
    <div>
        <vs-model :show.sync="show"
                  effect="fade"
                  width="600"
                  :large="true"
                  class="finder-model">
            <div slot="modal-header" class="modal-header">
                <h4 class="modal-title">Where to search?</h4>
            </div>
            <div slot="modal-body" class="modal-body">

                <div  v-if="errors.length > 0" class="alert alert-danger" role="alert">
                    <h4>Errors!!!</h4>
                    <ul>
                        <li class="text-danger" v-for="error in errors">{{ error }}</li>
                    </ul>
                </div>

                <div class="form-group create-keys-content">
                    <div class="finder-grid">
                        <div v-for="(folder, paths) in scopes"
                             v-if="paths.length > 0"
                             class="finder-grid-item">
                            <h3>{{ folder }}</h3>
                            <div class="list-group">
                                <div class="list-group-item"
                                       v-for="path in paths"
                                       track-by="$index"
                                >
                                    {{ folder }}/{{ path }}
                                    <button class="btn btn-danger btn-xs pull-right" @click.prevent="paths.$remove(path)">Delete</button>
                                </div>
                            </div>
                            <translator-finder-typeahead :folder="folder" @click-add="addPath"></translator-finder-typeahead>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <h3>Custom</h3>
                            <translator-finder-typeahead @click-add="addPath"></translator-finder-typeahead>
                        </div>
                    </div>
                </div>
                <vs-spinner id="group-modal-spinner-box" :text.sync="spinnerMsg" v-ref:spinner></vs-spinner>
            </div>
            <div slot="modal-footer" class="modal-footer">
                <button type="button"
                        class="btn btn-default"
                        @click='cancel()'
                        :disabled="isLoading"
                >
                    Close
                </button>

                <button type="button"
                        class="btn btn-success"
                        @click='save()'
                        :disabled="isLoading"
                >
                    Find translations
                </button>
            </div>
        </vs-model>
    </div>
</template>


<script type="text/ecmascript-6">

    import spinnerComponent from 'vue-strap/src/Spinner.vue';
    import modelComponent from 'vue-strap/src/Modal.vue';

    import FinderTypeaheadComponent from './FinderTypeahead.vue';

    export default{
        props: ['show'],

        data(){
            return {
                keys : '',
                spinnerMsg: 'creating keys...',
                focus: false,
                isLoading: false,
                scopes: window.translator.scopes,
                errors: []
            }
        },

        methods: {
            save() {
                let scopes = this.getPaths();

                this.loading(true);
                this.$http.post('finder/find', {scopes})
                        .then(function (res) {
                            let data = res.json();
                            this.success(data);
                        }, function (err) {
                            this.loading(false);
                        });
            },

            addPath(folder, path) {
                if(! folder) {
                    path = path.split('/');
                    folder = path.shift();
                    path = path.join('/');
                }

                if(! this.scopes[folder]) {
                    Vue.set(this.scopes, folder, []);
                }

                if(! _.isArray(this.scopes[folder])) {
                    this.scopes[folder] = [];
                }

                this.scopes[folder].push(path);
            },

            getPaths() {
                let allPaths = [];

                _.each(this.scopes, function(paths, folder) {
                    _.each(paths, function(path) {
                        allPaths.push(folder + '/' + path);
                    });
                });

                return allPaths;
            },

            loading(show) {
                if(show) {
                    this.$refs.spinner.show();
                    this.isLoading = true;

                    return;
                }

                this.$refs.spinner.hide();
                this.isLoading = false;
            },

            success(data) {
                this.loading(false);
                this.errors = data.errors;
                this.$root.onGroupsImported();
                if(data.errors.length == 0) {
                    this.close();
                }
            },

            cancel() {
                this.close();
            },

            close() {
                this.show = false;
            }

        },

        watch: {
            show: function (newVal, oldVal) {
                this.focus = newVal;
            }
        },

        components: {
            'vs-model': modelComponent,
            'vs-spinner': spinnerComponent,
            'translator-finder-typeahead': FinderTypeaheadComponent
        },

        computed: {

        },

        directives: {
        }

    }
</script>
