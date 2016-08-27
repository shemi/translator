<template xmlns:v-bind="http://www.w3.org/1999/xhtml">
    <div class="list-group translation-items-list">

        <div v-for="(key, group) in keys | filterBy filter"
             v-if="! group.st_model_id"
             v-bind:class="{'is-open': openList[key] || (filter && isGroup(group))}"
        >

            <translator-item-trigger v-bind:class="getGroupClasses(group)"
                                     tab-index="-1"
                                     href="#!"
                                     :group="group"
                                     :key.sync="key"
                                     :locales.sync="locales"
                                     @click="handelItemClicked(key, group)"
            ></translator-item-trigger>

            <translator-items :keys.sync="group"
                              :key="key"
                              :open-list.sync="openList"
                              :filter.sync="filter"
                              :selected.sync="selected"
                              id="sub-{{ key }}"
                              v-show="openList[key] || (filter && isGroup(group))"
                              v-if="isGroup(group)"
            >
            </translator-items>

        </div>

    </div>
</template>

<script type="text/ecmascript-6">

    import itemTriggerComponent from "./ItemTrigger.vue";

    import IsGroupMixin from "../Mixins/IsGroup";

    export default {

        name: 'translator-items',

        props: {
            'keys'    : {
                required: true,
                twoWay  : true
            },
            'key'    : {
                required: false,
                twoWay  : false
            },
            'filter'  : {
                required: false,
                twoWay  : true
            },
            'selected': {
                required: false,
                twoWay  : true
            },
            'openList': {
                required: true,
                twoWay  : true
            },
            'setAutoHeight': {
                required: false,
            }
        },

        data(){
            return {

            }
        },

        ready() {
            this.$on(this.key + '-next-item', function() {
                this.next();
            });
        },

        methods: {
            handelItemClicked(key, group) {
                if (this.isGroup(group)) {
                    Vue.set(this.openList, key, !this.openList[key]);
                    return;
                }

                this.selected = group;
            },

            isActive(group) {
                return this.selected && group[0] && this.selected[0].key == group[0].key;
            },

            getGroupClasses(group) {

                var isGroup = this.isGroup(group);

                return {
                    'list-group-item': true,
                    'active'         : this.isActive(group),
                    'is__group'      : isGroup,
                    'is__translation': ! isGroup
                }
            },

            getSelectedKey() {
                if(! this.selected) {
                    return;
                }

                var key = this.selected[0].key;
                var keys = key.split('.');

                return keys[keys.length - 1];
            },

            deleteItem(key) {
                return new Promise(function(resolve, reject) {
                    let keyPath,
                        group = this.keys[key];

                    if(! this.isGroup(group)) {
                        keyPath =  group[0].key;
                    } else {
                        keyPath = this.getGroupKeyPath(key);
                    }

                    this.$http.delete(window.translator.activeGroup, {
                        'params': {
                            'key': keyPath,
                            'isGroup': this.isGroup(group)
                        }
                    })
                    .then((res) => {

                        this.selected = null;
                        resolve();
                        Vue.delete(this.keys, key);
                        this.$dispatch('main-alert', true, {
                            title: "Deleted successfully",
                            description: "the translation deleted successfully.",
                        });
                    },
                    err => {
                        reject();
                    });
                }.bind(this));
            },

            getGroupKeyPath(key) {
                let parent = this.$parent;
                let keys = this.key ? [key, this.key] : [key];

                while (parent.$options.name == 'translator-items') {
                    if(parent.key) {
                        keys.push(parent.key);
                    }

                    parent = parent.$parent;
                }

                return keys.length > 1 ? keys.reverse().join('.') : keys[0];
            }

        },

        mixins: [IsGroupMixin],

        components: {
            'translator-item-trigger': itemTriggerComponent
        }

    }

</script>