<template>
    <div class="sidebar-container">

        <translator-group-list></translator-group-list>
    </div>
</template>


<script>

    import GroupListComponent from "./GroupList.vue";

    export default{
        data(){
            return{}
        },
        components:{
            'translator-group-list': GroupListComponent
        }
    }
</script>
