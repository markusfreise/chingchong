<script setup lang="ts">
import { ref } from 'vue'
import api from '@/api/client'
import type { Project, Task } from '@/types'
import { XMarkIcon } from '@heroicons/vue/24/outline'

const props = defineProps<{
  projects: Project[]
}>()

const emit = defineEmits<{
  close: []
  created: []
}>()

const projectId = ref('')
const taskId = ref('')
const description = ref('')
const date = ref(new Date().toISOString().split('T')[0]!)
const startTime = ref('09:00')
const endTime = ref('10:00')
const isBillable = ref(true)
const tasks = ref<Task[]>([])
const saving = ref(false)
const error = ref('')

async function loadTasks() {
  if (!projectId.value) return
  const { data } = await api.get(`/projects/${projectId.value}/tasks`)
  tasks.value = data.data
}

async function handleSave() {
  error.value = ''
  saving.value = true
  try {
    await api.post('/time-entries', {
      project_id: projectId.value,
      task_id: taskId.value || null,
      description: description.value || null,
      started_at: `${date.value}T${startTime.value}:00`,
      stopped_at: `${date.value}T${endTime.value}:00`,
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
            @change="taskId = ''; loadTasks()"
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

        <div class="form-row-3">
          <div class="form-group">
            <label class="form-label">Date *</label>
            <input v-model="date" type="date" class="form-input" required />
          </div>
          <div class="form-group">
            <label class="form-label">Start *</label>
            <input v-model="startTime" type="time" class="form-input" required />
          </div>
          <div class="form-group">
            <label class="form-label">End *</label>
            <input v-model="endTime" type="time" class="form-input" required />
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

.form-row-3 {
  @apply grid grid-cols-3 gap-3;
}

.form-checkbox-label {
  @apply flex items-center gap-2 text-sm text-gray-700;
}

.modal-actions {
  @apply flex justify-end gap-3 pt-2;
}
</style>
