<script setup lang="ts">
import { ref, onMounted } from 'vue'
import api from '@/api/client'
import type { Project, Client } from '@/types'
import { XMarkIcon } from '@heroicons/vue/24/outline'

const props = defineProps<{
  project: Project | null
  clients: Client[]
}>()

const emit = defineEmits<{
  close: []
  saved: []
}>()

const name = ref('')
const clientId = ref('')
const color = ref('#3B82F6')
const budgetHours = ref<number | null>(null)
const hourlyRate = ref<number | null>(null)
const isBillable = ref(true)
const saving = ref(false)
const error = ref('')

onMounted(() => {
  if (props.project) {
    name.value = props.project.name
    clientId.value = props.project.client_id
    color.value = props.project.color
    budgetHours.value = props.project.budget_hours ? Number(props.project.budget_hours) : null
    hourlyRate.value = props.project.hourly_rate ? Number(props.project.hourly_rate) : null
    isBillable.value = props.project.is_billable
  }
})

async function handleSave() {
  error.value = ''
  saving.value = true
  try {
    const payload = {
      name: name.value,
      client_id: clientId.value,
      color: color.value,
      budget_hours: budgetHours.value,
      hourly_rate: hourlyRate.value,
      is_billable: isBillable.value,
    }
    if (props.project) {
      await api.put(`/projects/${props.project.id}`, payload)
    } else {
      await api.post('/projects', payload)
    }
    emit('saved')
  } catch (e: any) {
    error.value = e.response?.data?.message || 'Failed to save'
  } finally {
    saving.value = false
  }
}
</script>

<template>
  <div class="modal-overlay" @click.self="emit('close')">
    <div class="modal-panel">
      <div class="modal-header">
        <h2 class="heading-2">{{ project ? 'Edit Project' : 'New Project' }}</h2>
        <button class="btn-ghost btn-icon" @click="emit('close')">
          <XMarkIcon class="modal-close-icon" />
        </button>
      </div>

      <form class="modal-body" @submit.prevent="handleSave">
        <div v-if="error" class="form-error-box">{{ error }}</div>

        <div class="form-group">
          <label class="form-label">Client *</label>
          <select v-model="clientId" class="form-select" required>
            <option value="">Select client</option>
            <option v-for="c in clients" :key="c.id" :value="c.id">{{ c.name }}</option>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label">Project Name *</label>
          <input v-model="name" type="text" class="form-input" required />
        </div>

        <div class="form-row-2">
          <div class="form-group">
            <label class="form-label">Color</label>
            <input v-model="color" type="color" class="form-input form-color" />
          </div>
          <div class="form-group">
            <label class="form-label">Billable</label>
            <label class="form-checkbox-label">
              <input v-model="isBillable" type="checkbox" class="form-checkbox" />
              <span>This project is billable</span>
            </label>
          </div>
        </div>

        <div class="form-row-2">
          <div class="form-group">
            <label class="form-label">Budget Hours</label>
            <input v-model.number="budgetHours" type="number" step="0.5" min="0" class="form-input" placeholder="e.g. 100" />
          </div>
          <div class="form-group">
            <label class="form-label">Hourly Rate (&euro;)</label>
            <input v-model.number="hourlyRate" type="number" step="0.01" min="0" class="form-input" placeholder="e.g. 120.00" />
          </div>
        </div>

        <div class="modal-actions">
          <button type="button" class="btn-secondary" @click="emit('close')">Cancel</button>
          <button type="submit" class="btn-primary" :disabled="saving">
            {{ saving ? 'Saving...' : 'Save' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<style scoped>
@reference "tailwindcss";
.modal-overlay {
  @apply fixed inset-0 z-50 flex items-center justify-center bg-black/50;
}

.modal-panel {
  @apply w-full max-w-lg rounded-xl bg-white shadow-xl;
}

.modal-header {
  @apply flex items-center justify-between px-6 py-4 border-b border-gray-200;
}

.modal-close-icon {
  @apply h-5 w-5;
}

.modal-body {
  @apply space-y-4 px-6 py-4;
}

.form-error-box {
  @apply rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700 border border-red-200;
}

.form-row-2 {
  @apply grid grid-cols-2 gap-4;
}

.form-color {
  @apply h-10 p-1 cursor-pointer;
}

.form-checkbox-label {
  @apply flex items-center gap-2 text-sm text-gray-700 mt-2;
}

.modal-actions {
  @apply flex justify-end gap-3 pt-2;
}
</style>
