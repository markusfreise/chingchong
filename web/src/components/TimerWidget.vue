<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useTimerStore } from '@/stores/timer'
import type { Project, Task } from '@/types'
import { PlayIcon, StopIcon } from '@heroicons/vue/24/solid'

const props = defineProps<{
  projects: Project[]
  tasks: Task[]
}>()

const timer = useTimerStore()

const selectedProjectId = ref('')
const selectedTaskId = ref('')
const description = ref('')

async function handleStart() {
  if (!selectedProjectId.value) return
  await timer.start(selectedProjectId.value, selectedTaskId.value || undefined, description.value || undefined)
  description.value = ''
}

async function handleStop() {
  await timer.stop()
}

onMounted(() => {
  if (timer.runningEntry) {
    selectedProjectId.value = timer.runningEntry.project_id
    selectedTaskId.value = timer.runningEntry.task_id || ''
    description.value = timer.runningEntry.description || ''
  }
})
</script>

<template>
  <div :class="timer.isRunning ? 'timer-card-running' : 'timer-card'">
    <div class="timer-top">
      <div class="timer-clock" :class="timer.isRunning ? 'timer-running' : 'timer-idle'">
        {{ timer.isRunning ? timer.elapsedFormatted : '0:00:00' }}
      </div>

      <button
        v-if="timer.isRunning"
        class="btn-danger btn-lg timer-btn"
        @click="handleStop"
      >
        <StopIcon class="timer-btn-icon" />
        Stop
      </button>
      <button
        v-else
        class="btn-success btn-lg timer-btn"
        :disabled="!selectedProjectId"
        @click="handleStart"
      >
        <PlayIcon class="timer-btn-icon" />
        Start
      </button>
    </div>

    <div v-if="timer.isRunning && timer.runningEntry" class="timer-running-info">
      <span class="color-dot" :style="{ backgroundColor: timer.runningEntry.project?.color }"></span>
      <span class="timer-running-project">{{ timer.runningEntry.project?.name }}</span>
      <span v-if="timer.runningEntry.task" class="timer-running-task">
        / {{ timer.runningEntry.task.name }}
      </span>
    </div>

    <div v-if="!timer.isRunning" class="timer-inputs">
      <div class="timer-row">
        <select v-model="selectedProjectId" class="form-select timer-select">
          <option value="">Select project...</option>
          <option v-for="project in projects" :key="project.id" :value="project.id">
            {{ project.client?.name ? `${project.client.name} / ` : '' }}{{ project.name }}
          </option>
        </select>

        <select v-model="selectedTaskId" class="form-select timer-select" :disabled="tasks.length === 0">
          <option value="">Select task (optional)</option>
          <option v-for="task in tasks" :key="task.id" :value="task.id">
            {{ task.name }}
          </option>
        </select>
      </div>

      <input
        v-model="description"
        type="text"
        class="form-input"
        placeholder="What are you working on?"
        @keydown.enter="handleStart"
      />
    </div>
  </div>
</template>

<style scoped>
@reference "tailwindcss";
.timer-card {
  @apply bg-white rounded-lg border border-gray-200 shadow-sm px-6 py-4;
}

.timer-card-running {
  @apply rounded-lg border border-green-300 shadow-sm px-6 py-4 bg-green-50;
}

.timer-top {
  @apply flex items-center justify-between mb-4;
}

.timer-clock {
  @apply font-mono text-3xl font-bold tabular-nums;
}

.timer-btn {
  @apply shrink-0;
}

.timer-btn-icon {
  @apply h-5 w-5;
}

.timer-running-info {
  @apply flex items-center gap-2 text-sm text-green-700;
}

.timer-running-project {
  @apply font-medium;
}

.timer-running-task {
  @apply text-green-600;
}

.timer-inputs {
  @apply space-y-3;
}

.timer-row {
  @apply grid grid-cols-1 sm:grid-cols-2 gap-3;
}

.timer-select {
  @apply w-full;
}
</style>
