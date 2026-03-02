<script setup lang="ts">
import { ref } from 'vue'
import api from '@/api/client'
import type { Project, Task } from '@/types'
import { XMarkIcon } from '@heroicons/vue/24/outline'

const props = defineProps<{
  projects: Project[]
  tasks: Task[]
}>()

const emit = defineEmits<{
  close: []
  created: []
}>()

const projectId = ref('')
const taskId = ref('')
const description = ref('')
const date = ref(new Date().toISOString().split('T')[0]!)
const duration = ref('')
const isBillable = ref(true)
const saving = ref(false)
const error = ref('')

function parseDuration(input: string): number | null {
  const trimmed = input.trim()
  if (!trimmed) return null

  // "1:30" or "1.30" -> 1h 30m, "1:" or "1." -> 1h 0m
  const match = trimmed.match(/^(\d+)[\:\.](\d{0,2})$/)
  if (match) {
    const hours = parseInt(match[1]!, 10)
    const minutes = match[2] ? parseInt(match[2]!, 10) : 0
    return (hours * 60 + minutes) * 60
  }

  // Plain number -> minutes
  const num = parseInt(trimmed, 10)
  if (!isNaN(num) && num > 0) {
    return num * 60
  }

  return null
}

function durationHint(): string {
  const seconds = parseDuration(duration.value)
  if (seconds === null) return ''
  const h = Math.floor(seconds / 3600)
  const m = Math.floor((seconds % 3600) / 60)
  if (h > 0 && m > 0) return `= ${h}h ${m}m`
  if (h > 0) return `= ${h}h`
  return `= ${m}m`
}

async function handleSave() {
  error.value = ''
  const seconds = parseDuration(duration.value)
  if (!seconds) {
    error.value = 'Enter a valid duration, e.g. 1:30, 1.5, or 90'
    return
  }

  saving.value = true
  try {
    await api.post('/time-entries', {
      project_id: projectId.value,
      task_id: taskId.value || null,
      description: description.value || null,
      date: date.value,
      duration_seconds: seconds,
      is_billable: isBillable.value,
      source: 'manual',
    })
    emit('created')
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
        <h2 class="heading-2">Manual Time Entry</h2>
        <button class="btn-ghost btn-icon" @click="emit('close')">
          <XMarkIcon class="modal-close-icon" />
        </button>
      </div>

      <form class="modal-body" @submit.prevent="handleSave">
        <div v-if="error" class="form-error-box">{{ error }}</div>

        <div class="form-group">
          <label class="form-label">Project *</label>
          <select
            v-model="projectId"
            class="form-select"
            required
            @change="taskId = ''"
          >
            <option value="">Select project</option>
            <option v-for="p in projects" :key="p.id" :value="p.id">
              {{ p.client?.name ? `${p.client.name} / ` : '' }}{{ p.name }}
            </option>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label">Task</label>
          <select v-model="taskId" class="form-select" :disabled="tasks.length === 0">
            <option value="">None</option>
            <option v-for="t in tasks" :key="t.id" :value="t.id">{{ t.name }}</option>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label">Description</label>
          <input v-model="description" type="text" class="form-input" placeholder="What did you work on?" />
        </div>

        <div class="form-row-2">
          <div class="form-group">
            <label class="form-label">Date *</label>
            <input v-model="date" type="date" class="form-input" required />
          </div>
          <div class="form-group">
            <label class="form-label">Duration *</label>
            <input
              v-model="duration"
              type="text"
              class="form-input"
              placeholder="1:30 or 90"
              required
            />
            <span v-if="durationHint()" class="form-hint">{{ durationHint() }}</span>
          </div>
        </div>

        <label class="form-checkbox-label">
          <input v-model="isBillable" type="checkbox" class="form-checkbox" />
          <span>Billable</span>
        </label>

        <div class="modal-actions">
          <button type="button" class="btn-secondary" @click="emit('close')">Cancel</button>
          <button type="submit" class="btn-primary" :disabled="saving || !projectId">
            {{ saving ? 'Saving...' : 'Save Entry' }}
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
  @apply grid grid-cols-2 gap-3;
}

.form-hint {
  @apply text-xs text-gray-500 mt-1;
}

.form-checkbox-label {
  @apply flex items-center gap-2 text-sm text-gray-700;
}

.modal-actions {
  @apply flex justify-end gap-3 pt-2;
}
</style>
