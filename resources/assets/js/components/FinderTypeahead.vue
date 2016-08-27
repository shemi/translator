<template>
    <div class="Typeahead">
        <i class="fa fa-spinner fa-spin" v-if="loading"></i>
        <template v-else>
            <i class="fa fa-search" v-show="isEmpty"></i>
            <i class="fa fa-times" v-show="isDirty" @click="reset"></i>
        </template>

        <div class="input-group">
            <div class="input-group-addon">{{ folder || 'root' }}/</div>
            <input type="text"
                   class="Typeahead__input form-control"
                   placeholder="{{ placeholder }}"
                   autocomplete="off"
                   v-model="query"
                   v-focus="focus"
                   @keydown.down="down"
                   @keydown.up="up"
                   @keydown.enter="hit"
                   @keydown.esc="reset"
                   @input="update"
                   @focus="update"
                   @blur="blur"

            />
            <div class="input-group-btn">
                <button class="btn btn-default" @click="add()">Add</button>
            </div>
        </div>


        <ul v-show="hasItems">
            <li v-for="item in items"
                :class="activeClass($index)"
                @mousedown="hit"
                @mousemove="setActive($index)"
            >
                <span class="name" v-text="item.name"></span>
                <span class="description dim" v-text="item.description"></span>
            </li>
        </ul>
    </div>
</template>



<script>

    import {focusModel as focusInput} from 'vue-focus';

    import VueTypeahead from 'vue-typeahead/src/main'

    export default {
        extends: VueTypeahead,

        props: ['folder'],

        data () {
            return {
                src: window.translator.baseUrl + '/finder/hint/',
                path: this.folder,
                limit: 0,
                focus: false,
                minChars: 0
            }
        },

        computed: {
            placeholder() {
                if(this.folder) {
                    return "Add folders to " + this.folder;
                }

                return "Add custom path";
            }
        },

        methods: {
            onHit (item) {
                this.query = item.path;
                this.update();
                Vue.nextTick(function() {
                    this.focus = true;
                }.bind(this));
            },

            prepareResponseData(res) {
                return JSON.parse(res);
            },

            blur() {
                this.items = [];
                this.loading = false;
            },

            update () {
                this.loading = true;

                this.fetch().then((response) => {
                    let data = response.data;
                    this.items = this.prepareResponseData(data);
                    this.current = -1;
                    this.loading = false;
                });
            },

            fetch () {
                const src = this.src;

                const params = {
                    'params': {
                        'path': this.query,
                        'folder': this.folder
                    }
                };

                return this.$http.get(src, params)
            },

            add() {
                if(! this.query) {
                    return;
                }

                this.$emit('click-add', this.folder, this.query);
                this.reset();
            }

        },

        directives: {
            focus: focusInput
        }
    }
</script>



<style scoped>
    .Typeahead {
        position: relative;
    }

    .fa-times {
        cursor: pointer;
    }

    i {
        float: right;
        position: relative;
        top: 30px;
        right: 29px;
        opacity: 0.4;
    }

    ul {
        position: absolute;
        padding: 0;
        margin-top: 8px;
        min-width: 100%;
        background-color: #fff;
        list-style: none;
        border-radius: 4px;
        box-shadow: 0 0 10px rgba(0,0,0, 0.25);
        z-index: 1000;
    }

    li {
        padding: 10px 16px;
        border-bottom: 1px solid #ccc;
        cursor: pointer;
    }

    li:first-child {
        border-radius: 4px 4px 0 0;
    }

    li:last-child {
        border-radius: 0 0 4px 4px;
        border-bottom: 0;
    }

    /*span {*/
        /*display: block;*/
        /*color: #2c3e50;*/
    /*}*/

    .active {
        background-color: #3aa373;
    }

    .active span {
        color: white;
    }

    .name {
        font-weight: 700;
        font-size: 18px;
    }

    .description {
        font-size: 85%;
    }
</style>