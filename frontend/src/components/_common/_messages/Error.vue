<template>
    <div>
        <template v-if="message">
            <div
                    v-for="message in displayMessages"
                    class="error">{{ message }}</div>
        </template>
        <template v-else-if="atPath">
            <error
                    v-for="message in messages"
                    :message="message"/>
        </template>
    </div>
</template>

<script>
    import {mapGetters} from 'vuex';
    import _ from 'lodash';

    export default {
        name: 'error',
        props: {
            message: {
                type: [String, Array],
                required: false,
            },
            atPath: {
                type: String,
                required: false,
            },
            context: {
                type: Object,
                required: false,
                default: () => {},
            },
            scope: {
                type: String,
                required: false,
            },
        },
        computed: {
            ...mapGetters([
                'validationMessagesFor',
                'validationMessages',
            ]),
            displayMessages() {
                let messages = this.message;
                if (!_.isArray(this.message)) {
                    messages = [this.message];
                }

                return messages;
            },
            messages() {
                if (!this.atPath) {
                    return [];
                }

                let messages = this.validationMessages;
                if (this.scope) {
                    messages = (messages.scoped && messages.scoped[this.scope]) || {};
                }

                return function(path, $context) {
                    try {
                        return eval(`this.${path}`);
                    } catch (e) {
                        return [];
                    }
                }.call(messages, this.atPath, this.context);
            },
        },
    };
</script>

<style scoped lang="scss">
    @import '~theme/variables';

    .error {
        background: $dangerColor;
        color: $blackColor;
        border-radius: 2px;
        padding: 10px 15px;
        margin: 10px 0 0 0;
    }
</style>
