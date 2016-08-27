const ImportGroupsMixin = {

    methods: {
        isGroup(group) {
            return (!group.st_model_id) && (!group[0] || !group[0].st_model_id);
        }
    }
};

export default ImportGroupsMixin;