<script setup>
defineProps({
    label: String,
});


</script>

<template>
    <v-text-field
        density="compact"
        variant="outlined"
        :label="label"
        @input="$emit('update:modelValue', $event.target.value)"
    ></v-text-field>
</template>
