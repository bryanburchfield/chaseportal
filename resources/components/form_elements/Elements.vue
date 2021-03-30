<template>
    <div class="el-tabs__inner">
        <el-row :gutter="10" class="row-bg" >
            <draggable :list="fields"
                :clone="clone"
                class="dragArea"
                :options="dropElementOptions"
                @start="onStart">
        <el-col :span="12" 
                :class="{ 'is-disabled': checkStopDragCondition(field) }"
                v-for="(field, index) in fields" 
                :key="index">
          <el-button class="button__sidebar" 
                     type="info">
            {{ field.text }}
          </el-button>
        </el-col>
      </draggable>
    </el-row>
  </div>
</template>

<script>
  import { FormBuilder } from '@/components/form_elements/form_builder'
  import draggable       from 'vuedraggable'

  export default {
    name: 'Elements',
    components: { draggable },
    data() {
      return {
        fields: FormBuilder.$data.fields,
        dropElementOptions: FormBuilder.$data.dropElementOptions
      };
    },
  }
</script>