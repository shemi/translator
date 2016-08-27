<template>
    <a :id="elementId">

        <span class="key">{{ key }}</span>
        <button class="btn btn-danger btn-link btn-xs" @click.prevent="deleteItem">Delete</button>

        <translator-locales-status :locales.sync="locales"
                                   :group="group"
                                   v-if="! isGroup(group)"
        ></translator-locales-status>

    </a>
</template>


<script>

    import IsGroupMixin from "../Mixins/IsGroup";

    import LocalesStatusComponent from "./LocalesStatus.vue";

    import swal from 'sweetalert2';

    export default {
        props: {
            'group'  : {
                required: true,
                twoWay  : false
            },
            'key'    : {
                required: false,
                twoWay  : true
            },
            'locales': {
                required: true,
                twoWay  : true
            }

        },

        methods: {
            deleteItem() {
                swal({
                    'text'               : 'Are you sure you want to delete the  <b>"' +
                                            this.key + '"</b> translation' +
                                            (this.isGroup(this.group) ? 's' : ''),

                    'title'              : 'Delete the translation' + (this.isGroup(this.group) ? 's?' : '?'),
                    'type'               : 'question',
                    'allowOutsideClick'  : false,
                    'confirmButtonText'  : 'Yes',
                    'cancelButtonText'   : 'No',
                    'focusCancel'        : true,
                    'showCancelButton'   : true,
                    'showLoaderOnConfirm': true,
                    'preConfirm': function() {
                        return this.$parent.deleteItem(this.key);
                    }.bind(this)
                });
            }
        },

        data(){
            return {}
        },

        mixins: [IsGroupMixin],

        computed: {
            elementId() {
                return this.group[0] && this.group[0].key ?
                this.group[0].key + '__selectable' :
                this.key + '__group-collapse-trigger';
            }
        },

        components: {
            'translator-locales-status': LocalesStatusComponent
        }
    }

</script>