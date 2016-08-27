<template>
    <div class="group-translator"
         v-focus="focusFilterField == true"
         style="min-height: 150px; position: relative">

        <translator-editor :selected.sync="selected" ></translator-editor>

        <div class="controls">
            <div class="form-inline">
                <button type="button" class="btn btn-primary" @click="showKeysModel = true">Add keys</button>

                <div class="form-group">
                    <label for="filter-translations" class="sr-only">Filter Keys</label>
                    <input type="text"
                           id="filter-translations"
                           class="form-control"
                           v-model="filterText"
                           style="width: 300px"
                           placeholder="Filter Keys">
                </div>

                <button type="button" class="btn btn-success pull-right" @click="publishGroup()">{{ publishBtnText }}</button>
            </div>
        </div>

        <translator-items id="main-translations-list"
                          v-scroll-to-selected="selectedId"
                          v-calc-height
                          :open-list.sync="openList"
                          :keys.sync="translations"
                          :filter.sync="filterText"
                          :selected.sync="selected"
        ></translator-items>

        <vs-spinner id="groups-spinner-box" :text.sync="spinnerMsg" v-ref:spinner></vs-spinner>

        <translator-keys-model :show.sync="showKeysModel" @keys-added="refreshTranslations"></translator-keys-model>

    </div>
</template>


<script type="text/ecmascript-6">

    import { focus } from 'vue-focus';
    import spinnerComponent from 'vue-strap/src/Spinner.vue';

    import ItemsComponent from './Items.vue';
    import EditorComponent from './Editor.vue';
    import KeysModelComponent from './KeysModel.vue';

    import ScrollToSelectedDirective from "../directives/ScrollToSelected";
    import CalcHeightDirective from "../directives/CalcHeight";

    export default {

        props: ['group'],

        data() {
            return {
                translations: {},
                openList: {},
                filterText: '',
                selected: null,
                selectedId: "",
                showKeysModel: false,
                focusFilterField: true,
                keysMap: [],
                publishBtnText: 'Publish Translations'
            }
        },

        methods: {
            getTranslations() {
                this.$refs.spinner.show();

                this.$http
                    .get(this.group)
                    .then(
                        function(res) {
                            var data = res.json();
                            this.$refs.spinner.hide();
                            this.translations = data.translations;
                            this.keysMap = data.keyMap;
                        },
                        function(err) {
                            this.$refs.spinner.hide();
                        }
                    );
            },

            refreshTranslations() {
                this.translations = {};
                this.getTranslations();
            },

            next() {
                let nextKey,
                    keysMap = this.keysMap;

                if(this.filterText) {
                    keysMap = this.filterTranslations();
                }

                if(this.selected) {
                    let currentKey = this.selected[0].key;
                    nextKey = keysMap[keysMap.indexOf(currentKey) + 1];
                }

                let key = nextKey ? nextKey : keysMap[0];

                this.updateSelected(key);
            },

            prev() {

                let prevKey,
                    keysMap = this.keysMap;

                if(this.filterText) {
                    keysMap = this.filterTranslations();
                }

                if(this.selected) {
                    let currentKey = this.selected[0].key;
                    prevKey = keysMap[keysMap.indexOf(currentKey) - 1];
                }

                let key = prevKey ? prevKey : keysMap[keysMap - 1];

                this.updateSelected(key);
            },

            updateSelected(key) {
                let splitKey = _.dropRight(key.split('.'));

                this.selected = _.get(this.translations, key, null);
                this.selectedId = this.selected[0].key + '__selectable';

                this.openList = {};

                if(splitKey.length > 0) {
                    while (splitKey.length > 0) {
                        Vue.set(this.openList, splitKey[0], true);
                        splitKey = _.drop(splitKey)
                    }
                }
            },

            filterTranslations(translations) {
                if(this.filterText === this.lastFilterText && ! translations && this.lastFilterdTranslation) {
                    return this.lastFilterdTranslation;
                }

                let translationSet = translations || this.translations;

                if(! this.filterText) {
                    return;
                }

                this.lastFilterText = this.filterText;
                let filteredTranslations = Vue.options.filters.filterBy(translationSet, this.filterText);

                filteredTranslations = _.map(filteredTranslations, function (value) {

                    value = value.$value || value;

                    if(_.isArray(value) && value[0].key) {
                        return value[0].key;
                    }

                    return this.filterTranslations(value);

                }.bind(this));

                return this.lastFilterdTranslation = _.flatMapDeep(filteredTranslations);
            },

            publishGroup() {

                let oldPublishBtnText = this.publishBtnText;
                this.publishBtnText = 'Publishing...';

                this.$http.post(window.translator.activeGroup + '/publish')
                        .then(function (res) {

                            this.publishBtnText = oldPublishBtnText;
                            this.setStatusSaved(res.json().updated);
                            this.$root.updateActiveGroupChangedCount('0');
                            this.$dispatch('main-alert', true, {
                                title: "Oh Yeah...",
                                description: "Published successfully.",
                            });

                        }, function (err) {
                            this.$dispatch('main-loading-stop');
                        });

            },

            setStatusSaved(keys) {
                _.forEach(keys, function (key) {
                    let translations = _.get(this.translations, key);
                    _.forEach(translations, function (translation) {

                        if(translation.value == "") {
                            return;
                        }

                        translation.status = window.translator.statuses.saved;
                    }.bind(this));
                }.bind(this));
            }

        },

        ready() {
            this.getTranslations();

            document.body.addEventListener("keyup",function(e){
                e = e || window.event;
                var key = e.which || e.keyCode; // keyCode detection
                var ctrl = e.ctrlKey ? e.ctrlKey : ((key === 17) ? true : false); // ctrl detection

                if(ctrl) {
                    switch (key) {
                        case 38: // ctrl + up
                            this.prev();
                            return false;
                            break;
                        case 40: // ctrl + down
                            this.next();
                            return false;
                            break;
                    }
                }

            }.bind(this),false);

        },

        events: {
            'new-groups-imported': function () {
                this.refreshTranslations();

                return true;
            }
        },

        components: {
            'vs-spinner': spinnerComponent,
            'translator-items': ItemsComponent,
            'translator-keys-model': KeysModelComponent,
            'translator-editor': EditorComponent
        },

        directives: {
            focus: focus,
            'calc-height': CalcHeightDirective,
            'scroll-to-selected': ScrollToSelectedDirective
        }
    }

</script>