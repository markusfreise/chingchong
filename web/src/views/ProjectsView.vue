<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/api/client'
import type { Project, Client } from '@/types'
import { PlusIcon } from '@heroicons/vue/24/outline'
import ProjectFormModal from '@/components/ProjectFormModal.vue'

const router = useRouter()
const projects = ref<Project[]>([])
const clients = ref<Client[]>([])
const loading = ref(true)
const showForm = ref(false)
const editingProject = ref<Project | null>(null)

async function fetchProjects() {
  loading.value = true
  try {
    const { data } = await api.get('/projects', {
      params: { 'filter[is_active]': true, per_page: 100, include_time_summary: true, sort: 'name' },
    })
    projects.value = data.data
  } finally {
    loading.value = false
  }
}

function openCreate() {
  editingProject.value = null
  showForm.value = true
}

function openEdit(project: Project) {
  editingProject.value = project
  showForm.value = true
}

async function archiveProject(project: Project) {
  if (!confirm(`Archive "${project.name}"?`)) return
  await api.delete(`/projects/${project.id}`)
  fetchProjects()
}

function onSaved() {
  showForm.value = false
  fetchProjects()
}

onMounted(async () => {
  const [, clientsRes] = await Promise.all([
    fetchProjects(),
    api.get('/clients', { params: { 'filter[is_active]': true, per_page: 100 } }),
  ])
  clients.value = clientsRes.data.data
})
</script>

<template>
  <div class="page-container">
    <div class="page-header">
      <h1 class="heading-1">Projects</h1>
      <button class="btn-primary" @click="openCreate">
        <PlusIcon class="btn-icon-sm" />
        New Project
      </button>
    </div>

    <div v-if="loading" class="loading-center">
      <div class="loading-spinner"></div>
    </div>

    <div v-else class="projects-grid">
      <div
        v-for="project in projects"
        :key="project.id"
        class="project-card"
        @click="router.push({ name: 'project-detail', params: { id: project.id } })"
      >
        <div class="project-card-header">
          <span class="color-dot" :style="{ backgroundColor: project.color }"></span>
          <div class="project-card-info">
            <h3 class="project-card-name">{{ project.name }}</h3>
            <span class="text-muted">{{ project.client?.name }}</span>
          </div>
          <div class="project-card-actions" @click.stop>
            <button class="btn-ghost btn-sm" @click="openEdit(project)">Edit</button>
            <button class="btn-ghost btn-sm" @click="archiveProject(project)">Archive</button>
          </div>
        </div>

        <div class="project-card-stats">
          <div class="project-stat">
            <span class="project-stat-label">Tracked</span>
            <span class="project-stat-value">{{ project.total_tracked_hours ?? 0 }}h</span>
          </div>
          <div v-if="project.budget_hours" class="project-stat">
            <span class="project-stat-label">Budget</span>
            <span class="project-stat-value">{{ project.budget_hours }}h</span>
          </div>
          <div v-if="project.hourly_rate" class="project-stat">
            <span class="project-stat-label">Rate</span>
            <span class="project-stat-value">&euro;{{ project.hourly_rate }}/h</span>
          </div>
          <div class="project-stat">
            <span class="project-stat-label">Tasks</span>
            <span class="project-stat-value">{{ project.tasks_count ?? 0 }}</span>
          </div>
        </div>

        <div v-if="project.budget_hours && project.budget_used_percentage != null" class="project-budget-bar">
          <div class="budget-bar-bg">
            <div
              class="budget-bar-fill"
              :class="{
                'budget-bar-ok': project.budget_used_percentage < 80,
                'budget-bar-warn': project.budget_used_percentage >= 80 && project.budget_used_percentage < 100,
                'budget-bar-over': project.budget_used_percentage >= 100,
              }"
              :style="{ width: Math.min(project.budget_used_percentage, 100) + '%' }"
            ></div>
          </div>
          <span class="budget-bar-text">{{ project.budget_used_percentage }}%</span>
        </div>

        <div class="project-card-footer">
          <span :class="project.is_billable ? 'badge-green' : 'badge-gray'">
            {{ project.is_billable ? 'Billable' : 'Non-billable' }}
          </span>
        </div>
      </div>
    </div>

    <ProjectFormModal
      v-if="showForm"
      :project="editingProject"
      :clients="clients"
      @close="showForm = false"
      @saved="onSaved"
    />
  </div>
</template>

<style scoped>
@reference "tailwindcss";
.page-header {
  @apply flex items-center justify-between mb-6;
}

.loading-center {
  @apply flex justify-center py-12;
}

.projects-grid {
  @apply grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4;
}

.project-card {
  @apply bg-white rounded-lg border border-gray-200 shadow-sm p-4 cursor-pointer hover:shadow-md transition-shadow;
}

.project-card-header {
  @apply flex items-start gap-3 mb-4;
}

.project-card-info {
  @apply flex-1 min-w-0;
}

.project-card-name {
  @apply text-sm font-semibold text-gray-900 truncate;
}

.project-card-actions {
  @apply flex gap-1 shrink-0;
}

.project-card-stats {
  @apply grid grid-cols-2 gap-2 mb-3;
}

.project-stat {
  @apply flex flex-col;
}

.project-stat-label {
  @apply text-xs text-gray-500;
}

.project-stat-value {
  @apply text-sm font-semibold text-gray-900 tabular-nums;
}

.project-budget-bar {
  @apply flex items-center gap-2 mb-3;
}

.budget-bar-bg {
  @apply flex-1 h-2 rounded-full bg-gray-200 overflow-hidden;
}

.budget-bar-fill {
  @apply h-full rounded-full transition-all;
}

.budget-bar-ok {
  @apply bg-green-500;
}

.budget-bar-warn {
  @apply bg-yellow-500;
}

.budget-bar-over {
  @apply bg-red-500;
}

.budget-bar-text {
  @apply text-xs font-medium text-gray-500 tabular-nums w-10 text-right;
}

.project-card-footer {
  @apply flex items-center gap-2;
}

.btn-icon-sm {
  @apply h-4 w-4;
}
</style>
