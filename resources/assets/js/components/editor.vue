<template>
    <div class="panel translator-editor">
        <div class="panel-body">
            <div class="selected-key">
                <p v-if="selected" class="lead">Translate: {{{ selected[0].key | lastKey }}}</p>
                <p v-else class="lead">Select a key to translate</p>
            </div>

            <div class="editor-tabs-panel">

                <translator-saving id="translator__saving_indicator" v-ref:saving></translator-saving>

                <vs-tabs :active.sync="activeLocale">
                    <vs-tab v-for="locale in locales"
                            :header="locale.name">
                        <div>
                            <div class="form-group">
                                <textarea class="form-control"
                                          name="editor"
                                          rows="3"
                                          :disabled="! selectedLocale.locale"
                                          v-model="selectedLocale.value"
                                          :style="{direction: locale.isRtl ? 'rtl' : 'ltr'}"
                                          debounce="150"
                                          v-on:keyup="autoSave"
                                          id="editor-{{ locale.code }}">
                                </textarea>
                            </div>
                        </div>
                    </vs-tab>
                </vs-tabs>

            </div>

        </div>
    </div>
</template>


<script type="text/ecmascript-6">


    //TODO: fix selected locale deleted after import


    import TabsComponent from "vue-strap/src/Tabset.vue";
    import TabComponent from "vue-strap/src/Tab.vue";

    import SavingIndicatorComponent from "./SavingIndicator.vue";

    export default {

        name: 'translator-editor',

        props: {
            'selected': {
                required: true
            }
        },

        data(){
            return {
                activeLocale  : 0,
                selectedLocale: {},
                oldValue      : '',
                clock         : false,
                locales       : window.translator.locales
            }
        },

        methods: {
            setSelectedByLocal(locale) {

                if(!this.selected || !this.selected[0]) {
                    return;
                }


                this.selectedLocale = _.find(this.selected, ['locale', locale]);

                if(!this.selectedLocale) {
                    var position = this.selected.length;

                    this.selected.push({
                        value : '',
                        key   : this.selected[0].key,
                        group : this.selected[0].group,
                        locale: locale,
                        isNew : true
                    });

                    this.selectedLocale = this.selected[position];
                }

                this.oldValue = this.selectedLocale.value;

            },

            restartEditor() {
                if(this.clock) {
                    clearTimeout(this.clock);
                }

            },

            getActiveLocale() {
                return this.activeLocale ? this.locales[this.activeLocale].code : this.locales[0].code;
            },

            autoSave() {
                if(this.clock) {
                    clearTimeout(this.clock);
                    this.clock = false;
                }

                var data = this.selectedLocale;

                this.clock = setTimeout(function() {
                    Vue.nextTick(function() {
                        this.save(data);
                    }.bind(this));
                }.bind(this), 200);
            },

            save(data) {
                var self = this;
                data = data || self.selectedLocale;

                if(!data.value || self.oldValue == data.value) {
                    return;
                }

                self.$refs.saving.start();

                self.$http.put(data.group + '/update', self.selectedLocale)
                        .then(
                                function(res) {
                                    let translation = res.json().translation;
                                    if(data.status !== window.translator.statuses.changed) {
                                        self.$root.updateActiveGroupChangedCount('+1');
                                    }

                                    data.status = translation.status;
                                    self.$refs.saving.end();

                                },

                                function(err) {
                                    self.$refs.saving.end();
                                }
                        );

                self.oldValue = data.value;
            }

        },

        watch: {
            activeLocale: function(newLocale, oldLocale) {
                this.setSelectedByLocal(this.locales[newLocale].code);
            },

            selected: function(newSelected, oldSelected) {

                if(!oldSelected || (newSelected && newSelected[0].key !== oldSelected[0].key)) {
                    this.setSelectedByLocal(this.getActiveLocale());
                }

                if(! newSelected) {
                    this.selectedLocale = {};
                }

            }

        },

        filters: {
            lastKey: (string) => {
                var keys = string.split('.');
                var last = keys.pop();

                string = keys.join('.');
                string += string ? "." : "";
                string += "<b>" + last + "</b>";
                return string;
            }
        },

        components: {
            'vs-tabs'          : TabsComponent,
            'vs-tab'           : TabComponent,
            'translator-saving': SavingIndicatorComponent
        }
    }

</script>