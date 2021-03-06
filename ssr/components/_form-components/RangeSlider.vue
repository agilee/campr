<template>
    <div class="slider-holder">
        <div class="heading flex flex-space-between">
            <span class="title">{{ title }}</span>
            <span class="value">
                <span class="text">{{ minPrefix}}</span>
                <span class="from number" v-text="rangeSliderModel | valueInterval"></span>
                <span class="text">{{ minSuffix }}</span>
            </span>
        </div>

        <input type="text" class="range" v-bind:id="'slider' + _uid" ref="slider" v-model="rangeSliderModel" />
    </div>
</template>

<script>
import DeferredCallbackQueue from 'deferred-callback-queue';

export default {
    props: [
        'title',
        'min',
        'max',
        'type',
        'minPrefix',
        'minSuffix',
        'maxPrefix',
        'maxSuffix',
        'values',
        'value',
        'disabled',
        'step',
        'model',
        'modelName',
    ],
    computed: {
        from() {
            if (!this.value) {
                return this.min;
            }
            const values = this.value.split(';');
            return values[0];
        },
        to() {
            if (!this.value) {
                return this.from;
            }
            const values = this.value.split(';');
            return values.length > 1 ? values[1] : this.max;
        },
    },
    mounted() {
        const $this = window.$('#slider' + this._uid);
        const values = this.values ? this.values.split(',') : '';

        this.rangeSliderModel = this.model;
        const vm = this;

        let valueTmp;
        if (this.type === 'double') {
            if (typeof valueTmp !== 'undefined') {
                valueTmp = this.model.split(';');
                valueTmp[0] = parseInt(valueTmp[0]);
                valueTmp[1] = parseInt(valueTmp[1]);
            } else {
                valueTmp = [0, 0];
            }
        } else {
            valueTmp = this.model;
        }

        let rangeParams = {
            type: this.type,
            min: this.min,
            max: this.max,
            from: null,
            to: null,
            values: values,
            disable: this.disabled,
            step: this.step,
            onFinish(data) {
                vm.finishChangingValue(data.from);
            },
        };

        if (this.type == 'double') {
            rangeParams.from = (values instanceof Array) ? values.indexOf(valueTmp[0]) : valueTmp[0];
            rangeParams.to = (values instanceof Array) ? values.indexOf(valueTmp[1]) : valueTmp[1];
        } else {
            rangeParams.from = (values instanceof Array) ? values.indexOf(valueTmp) : valueTmp;
        }

        $this.ionRangeSlider(rangeParams);
    },
    methods: {
        getDeferredSaveQueue() {
            if (this.deferredQueue) {
                return this.deferredQueue;
            } else {
                this.deferredQueue = new DeferredCallbackQueue(1000, true);
                return this.deferredQueue;
            }
        },
        finishChangingValue(value) {
            this.rangeSliderModel = value;
            this.$emit('onRangeSliderUpdate', {value: value, modelName: this.modelName});
        },
    },
    watch: {
        value(val) {
            const $slider = window.$('#slider' + this._uid);
            this.rangeSliderModel = parseInt(val, 10);
            if (!$slider.length) {
                return;
            }

            const irs = $slider.data().ionRangeSlider;
            if (irs && !irs.dragging) {
                irs.update({
                    from: val,
                });
            }
        },
        disabled(val) {
            const $slider = window.$('#slider' + this._uid);
            if (!$slider.length) {
                return;
            }

            const irs = $slider.data().ionRangeSlider;
            if (irs) {
                irs.update({
                    disable: val,
                });
            }
        },
    },
    filters: {
        valueInterval(value) {
            if (typeof value === 'string') {
                return value.replace(';', ' - ');
            }
            return value;
        },
    },
    data() {
        return {
            rangeSliderModel: 0,
        };
    },
};
</script>

<style lang="scss" scoped>
    @import '../../../frontend/src/css/_variables';
    @import '../../node_modules/ion-rangeslider/css/ion.rangeSlider.css';
    @import '../../node_modules/ion-rangeslider/css/ion.rangeSlider.skinHTML5.css';

    .irs-min, .irs-max, .irs-from, .irs-to, .irs-single {
        display: none !important;
        visibility: hidden !important;
    }

    .irs-line {
        background: $darkColor !important;
        border: none !important;
    }

    .irs-bar {
        background: $middleColor !important;
        border: none !important;
    }

    .irs-bar-edge {
        background: $middleColor !important;
        border: none !important;
    }

    .irs-slider {
        font-size: 0 !important;
        background: $secondColor !important;
        border: 2px solid $secondDarkColor !important;
    }

    .task-sidebar {
        .irs-line {
            background: $middleColor !important;
            border: none !important;
        }

        .irs-bar,
        .irs-bar-edge {
            background: $secondColor !important;
            border: none !important;
        }
    }
</style>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped lang="scss">
  @import '../../../frontend/src/css/_variables';

  .slider-holder {
      text-transform: uppercase;
      color: $lightColor;
      position: relative;
      margin-bottom: 35px;

      .heading {
          position: absolute;
          width: 100%;
      }

      .title {
          letter-spacing: 1.9px;
      }

      .value {
          letter-spacing: 1.6px;
      }

      .number {
          color: $secondColor;
      }

      .slider {
          margin-top: 9px;
          width: 100%;
          height: 11px;
          padding: 0;
      }

      .range-slider-rail, .range-slider-fill {
          height: 10px;
          border-radius: 5px;
      }

      .range-slider-rail {
          background: $darkColor;
      }

      .range-slider-fill {
          background: $middleColor;
      }

      .range-slider-knob {
          background: $secondColor;
          border: 2px solid $secondDarkColor;
      }
  }
</style>
