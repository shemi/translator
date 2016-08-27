<template>
    <div>

        <div v-if="groups.length <= 0"
             class="text-center">
            <p class="lead">
                No Groups Founds
                <br><br>
                <b>To import groups from the translation files</b>
            </p>
            <button @click="importGroups(false)"
                    class="btn btn-primary">
                Click Hare
            </button>
        </div>

        <div class="text-center"
             v-if="groups.length > 0"
             style="margin-bottom: 15px">
            <input type="text"
                   class="form-control"
                   v-focus="focusFilterField"
                   v-model="filterText"
                   placeholder="Filter Groups">
        </div>

        <ul class="nav nav-pills nav-stacked">
            <li role="presentation"
                v-for="group in groups | filterBy filterText in 'key'"
                v-bind:class="{active: activeGroup == group.key}"
            >
                <a href="{{ groupUrl(group.key) }}">
                    {{ group.key }}
                    <span class="badge danger" v-if="group.changed_count != 0">
                        {{ group.changed_count }}
                    </span>
                </a>
            </li>
        </ul>

        <vs-spinner id="groups-spinner-box" :text.sync="spinnerMsg" v-ref:groupsspinner></vs-spinner>
    </div>
</template>


<script type="text/ecmascript-6">

    import GetGroupsMixin from "../Mixins/GetGroups";
    import ImportGroupsMixin from "../Mixins/ImportGroups";

    import { focusModel } from 'vue-focus';
    import spinnerComponent from 'vue-strap/src/Spinner.vue';

    console.log(GetGroupsMixin);

    export default{

        data(){
            return {
                filterText: '',
                focusFilterField: window.translator.activeGroup == '',
                groups: []
            }
        },

        ready() {
            this.getGroups();
        },

        events: {
            'new-groups-imported': function() {
                this.refreshGroups();

                return true;
            },
            'groups-imported': function() {
                this.refreshGroups();

                return true;
            },
            'update-active-group-changed-count': function(to) {
                this.updateActiveGroupChangedCount(to);
            }
        },

        methods: {

            refreshGroups() {
                this.getGroups();
                this.focusFilterField = true;
            },

            groupUrl: (key) => {
                return window.translator.baseUrl + "/groups/" + key;
            },

            updateActiveGroupChangedCount(to) {
                let currentKey = _.find(this.groups, {'key': this.activeGroup});

                switch(to) {
                    case '+1':
                        currentKey.changed_count += 1;
                        break;
                    case '0':
                        currentKey.changed_count = 0;
                        break;
                }
            }
        },

        watch: {
            'gettingGroups': (function (status) {
                if (status) {
                    this.$refs.groupsspinner.show();
                } else {
                    this.$refs.groupsspinner.hide();
                }
            })
        },

        computed: {
            activeGroup: () => {
                return window.translator.activeGroup;
            }
        },

        mixins: [
            GetGroupsMixin,
            ImportGroupsMixin
        ],

        components: {
            'vs-spinner': spinnerComponent
        },

        directives: { focus: focusModel }
    }
</script>