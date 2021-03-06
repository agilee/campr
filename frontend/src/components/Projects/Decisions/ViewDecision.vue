<template>
    <div>
        <div class="row">
            <div class="col-md-6">
                <div class="view-todo page-section">
                    <!-- /// Header /// -->
                    <modal v-if="showDeleteModal" @close="showDeleteModal = false">
                        <p class="modal-title">{{ translate('message.delete_decision') }}</p>
                        <div class="flex flex-space-between">
                            <a href="javascript:void(0)" @click="showDeleteModal = false" class="btn-rounded btn-auto">{{ translate('message.no') }}</a>
                            <a href="javascript:void(0)" @click="removeDecision()" class="btn-rounded btn-empty btn-auto danger-color danger-border">{{ translate('message.yes') }}</a>
                        </div>
                    </modal>
                    <modal v-if="showRescheduleModal" @close="cancelRescheduleModal()" v-bind:hasSpecificClass="true">
                        <p class="modal-title">{{ translate('message.reschedule_decision') }}</p>
                        <div class="form-group last-form-group">
                            <div class="col-md-12">
                                <div class="input-holder">
                                    <label class="active">{{ translate('label.select_due_date') }}</label>
                                    <date-field v-model="reschedule.dueDate"/>
                                </div>
                            </div>
                        </div>
                        <hr class="double">

                        <div class="flex flex-space-between">
                            <a href="javascript:void(0)" @click="cancelRescheduleModal()" class="btn-rounded btn-auto">{{ translate('button.cancel') }}</a>
                            <a href="javascript:void(0)" @click="rescheduleDecision()" class="btn-rounded btn-auto second-bg">{{ translate('button.save') }}</a>
                        </div>
                    </modal>

                    <div class="header flex-v-center">
                        <div>
                            <router-link :to="{name: 'project-decisions'}" class="small-link">
                                <i class="fa fa-angle-left"></i>
                                {{ translate('message.back_to_decisions') }}
                            </router-link>
                            <h1>{{ currentDecision.title }}</h1>
                            <h3 class="category"><b>{{ currentDecision.meetingName }}</b> | <b>{{ currentDecision.decisionCategoryName }}</b></h3>
                            <h4>
                                {{ translate('message.created') }}: <b>{{ currentDecision.createdAt | date }}</b>
                                | {{ translate('message.due_date') }}: <b>{{currentDecision.dueDate | date }}</b>|
                                {{ translate('label.distribution_list') }}: <b v-if="currentDecision.distributionList">{{ translate(currentDecision.distributionListName) }}</b><b v-else>-</b>
                            </h4>
                            <div class="entry-responsible flex flex-v-center">
                                <user-avatar
                                    size="small"
                                    :url="currentDecision.responsibilityAvatarUrl"
                                    :name="currentDecision.responsibilityFullName"/>
                                <div>
                                    {{ translate('message.responsible') }}:
                                    <b>{{currentDecision.responsibilityFullName}}</b>
                                </div>
                            </div>
                            <a @click="showRescheduleModal = true" class="btn-rounded btn-auto btn-md btn-empty">{{ translate('button.reschedule') }} <reschedule-icon></reschedule-icon></a>
                        </div>
                    </div>
                    <!-- /// End Header /// -->
                </div>

                <div class="entry-body">
                    <div v-html="currentDecision.description"></div>
                </div>
                <hr class="double">
                <!-- /// Decision Attachments /// -->
                <h3>{{ translate('message.attachments') }}</h3>
                <div class="attachments">
                    <template v-for="(media, index) in currentDecision.medias">
                        <div
                            class="attachment"
                            v-if="media"
                            :key="index">
                            <view-icon/>
                            <span class="attachment-name">
                                <a @click="getMediaFile(media)" v-if="media.id">{{ media.name }}</a>
                                <span v-else>{{ media.name }}</span>
                            </span>
                        </div>
                    </template>
                </div>
                <!-- /// End Decision Attachments /// -->
            </div>
            <div class="col-md-6">
                <div class="create-meeting page-section">
                    <!-- /// Header /// -->
                    <div class="margintop20 text-right">
                        <div class="buttons">
                            <router-link class="btn-rounded btn-auto" :to="{name: 'project-decisions-edit-decision', params:{decisionId: currentDecision.id}}">
                                {{ translate('button.edit_decision') }}
                            </router-link>
                            <router-link :to="{name: 'project-decisions-create-decision'}" class="btn-rounded btn-auto second-bg">
                                {{ translate('button.new_decision') }}
                            </router-link>
                            <a @click="showDeleteModal = true" class="btn-rounded btn-auto danger-bg">{{ translate('button.delete_decision') }}</a>
                        </div>
                    </div>
                    <!-- /// End Header /// -->
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="text-right footer-buttons">
                    <div class="buttons">
                        <router-link class="btn-rounded btn-auto" :to="{name: 'project-decisions-edit-decision', params:{decisionId: currentDecision.id}}">
                            {{ translate('button.edit_decision') }}
                        </router-link>
                        <router-link class="btn-rounded btn-auto second-bg" :to="{name: 'project-decisions-create-decision'}">
                            {{ translate('button.new_decision') }}
                        </router-link>
                        <a @click="showDeleteModal = true" class="btn-rounded btn-auto danger-bg">{{ translate('button.delete_decision') }}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import EditIcon from '../../_common/_icons/EditIcon';
import DeleteIcon from '../../_common/_icons/DeleteIcon';
import Switches from '../../3rdparty/vue-switches';
import RescheduleIcon from '../../_common/_icons/RescheduleIcon';
import {mapActions, mapGetters} from 'vuex';
import Modal from '../../_common/Modal';
import moment from 'moment';
import router from '../../../router';
import DateField from '../../_common/_form-components/DateField';
import UserAvatar from '../../_common/UserAvatar';
import Vue from 'vue';

export default {
    components: {
        UserAvatar,
        DateField,
        EditIcon,
        DeleteIcon,
        Switches,
        RescheduleIcon,
        Modal,
        moment,
    },
    methods: {
        ...mapActions(['getDecision', 'editDecision', 'deleteDecision']),
        getMediaFile(media) {
            if (!media.id) {
                return;
            }

            const url = Routing.generate('app_api_media_download', {id: media.id});
            Vue.http.get(url, {responseType: 'blob'})
                .then((response) => {
                    if (response.status !== 200) {
                        return;
                    }

                    let options = {};
                    if (response.headers && response.headers.map && response.headers.map['content-type']) {
                        options.type = response.headers.map['content-type'][0];
                    }

                    let blob = new Blob([response.body], options);
                    let a = document.createElement('a');
                    a.href = window.URL.createObjectURL(blob);
                    a.download = media.originalName;
                    document.body.appendChild(a);
                    a.click();

                    setTimeout(() => {
                        document.body.removeChild(a);
                        window.URL.revokeObjectURL(url);
                    }, 100);
                });
        },
        rescheduleDecision: function() {
            let data = {
                id: this.$route.params.decisionId,
                dueDate: moment(this.reschedule.dueDate).format('DD-MM-YYYY'),
            };
            this.editDecision(data);
            this.showRescheduleModal = false;
        },
        cancelRescheduleModal: function() {
            this.showRescheduleModal = false;
            this.reschedule.dueDate = new Date(this.currentDecision.dueDate);
        },
        removeDecision: function() {
            if (this.$route.params.decisionId) {
                this.deleteDecision({id: this.$route.params.decisionId});
                this.showDeleteModal = false;
                router.push({name: 'project-decisions', params: {id: this.$route.params.id}});
            }
        },
    },
    computed: {
        ...mapGetters({currentDecision: 'currentDecision'}),
    },
    created() {
        if (this.$route.params.decisionId) {
            this.getDecision(this.$route.params.decisionId);
        }
    },
    data() {
        return {
            showDeleteModal: false,
            showRescheduleModal: false,
            reschedule: {
                dueDate: moment().toDate(),
            },
        };
    },
    watch: {
        currentDecision(val) {
            this.reschedule.dueDate = this.currentDecision.dueDate ? moment(this.currentDecision.dueDate).toDate() : null;
        },
    },
};
</script>

<style lang="scss">
    @import '../../../css/_mixins';
    @import '~theme/variables';

    ul.attachments {
        li {
            a {
                .icon {
                    svg {
                        width: 12px;
                        height: 12px;
                        @include transition(color, 0.2s, ease);
                    }
                }

                &:hover {
                    .icon {
                        svg {
                            fill: $secondDarkColor;
                        }
                    }
                }
            }
        }
    }
</style>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped lang="scss">
    @import '../../../css/_mixins';
    @import '~theme/variables';

    .user-avatar {
        width: 30px;
        height: 30px;
        display: inline-block;
        margin: 0 10px 0 0;
        position: relative;
        top: -2px;
        background-size: cover;
        background-position: center center;
        background-repeat: no-repeat;
        @include border-radius(50%);
    }

    .entry-body {
        ul {
            list-style-type: disc;
            list-style-position: inside;

            &:last-child {
                margin-bottom: 0;
            }
        }

        ol {
            list-style-type: decimal;
            list-style-position: inside;
            padding: 0;

            &:last-child {
                margin-bottom: 0;
            }
        }

        img {
            @include box-shadow(0, 0, 20px, $darkColor);
        }
    }

    .entry-responsible {
        text-transform: uppercase;
        letter-spacing: 0.1em;
        font-size: 10px;
        line-height: 1.5em;
        margin: 20px 0;

        b {
            display: block;
            font-size: 12px;
        }
    }

    .category {
        margin-top: 0;
    }

    .footer-buttons {
        margin-top: 60px;
        padding: 30px 0;
        border-top: 1px solid $darkerColor;
    }

    .buttons {
      a {
        margin: 5px 0 5px 10px;
      }
    }

    div.attachments {
        margin: 0 0 20px;

        .attachment {
            padding: 10px 20px;
            background-color: $fadeColor;
            margin-top: 3px;
            color: $secondColor;
            position: relative;

            .view-icon {
                display: inline;
                margin-right: 10px;
                position: relative;
                top: 3px;

                svg {
                    width: 18px;
                }
            }
            .attachment-name {
                a {
                    cursor: pointer;
                }
            }
        }
    }

</style>
