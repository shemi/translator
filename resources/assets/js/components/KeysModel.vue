<template>
    <div>
        <vs-model :show.sync="show"
                  effect="zoom"
                  width="400"
                  large="true"
                  class="keys-model"
                  backdrop="false">
            <div slot="modal-header" class="modal-header">
                <h4 class="modal-title">Create new keys</h4>
            </div>
            <div slot="modal-body" class="modal-body">
                <div class="form-group create-keys-content">
                    <label for="keys-insert" class="sr-only">Create new keys</label>
                    <textarea id="keys-insert"
                              class="form-control keys-input"
                              rows="5"
                              placeholder="One key per line, without the group prefix."
                              v-focus="focus"
                              v-model="keys"></textarea>

                    <vs-spinner id="group-modal-spinner-box" :text.sync="spinnerMsg" v-ref:spinner></vs-spinner>

                </div>
            </div>
            <div slot="modal-footer" class="modal-footer">
                <button type="button"
                        class="btn btn-default"
                        @click='cancel()'
                        :disabled="isLoading"
                >
                    Cancel
                </button>

                <button type="button"
                        class="btn btn-success"
                        @click='save()'
                        :disabled="isLoading"
                >
                    Add
                </button>
            </div>
        </vs-model>
    </div>
</template>


<script type="text/ecmascript-6">

    import {focusModel} from 'vue-focus';
    import spinnerComponent from 'vue-strap/src/Spinner.vue';
    import modelComponent from 'vue-strap/src/Modal.vue';

    export default{
        props: ['show'],

        data(){
            return {
                keys : '',
                spinnerMsg: 'creating keys...',
                focus: false,
                isLoading: false,
                group: window.translator.activeGroup
            }
        },

        methods: {
            save() {

                if(! this.keys) {
                    return;
                }

                this.loading(true);

                this.$http
                        .post('groups', {
                            'group': this.group,
                            'keys': this.keys
                        })
                        .then(
                                function (res) {
                                    let data = res.json();
                                    this.success(data);
                                    this.$dispatch('main-alert', true, {
                                        title: "Yeah...",
                                        description: "The keys added.",
                                    });
                                },
                                function (err) {
                                    this.loading(false);
                                }
                        );
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
                this.$emit('keys-added', data.count);
                this.keys = "";
                this.close();

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
        },

        computed: {

        },

        directives: {
            focus: focusModel
        }

    }
</script>
