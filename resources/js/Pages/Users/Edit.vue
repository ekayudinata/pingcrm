<template>
  <div>
    <Head :title="`${form.first_name} ${form.last_name}`" />

    <div class="flex justify-start mb-8 max-w-3xl">
      <h1 class="text-3xl font-bold">
        <Link class="text-indigo-400 hover:text-indigo-600" href="/users">Users</Link>
        <span class="text-indigo-400 font-medium">/</span>
        {{ form.first_name }} {{ form.last_name }}
      </h1>
      <img v-if="user.photo" class="block ml-4 w-8 h-8 rounded-full" :src="user.photo" />
    </div>
    <trashed-message v-if="user.deleted_at" class="mb-6" @restore="restore"> This user has been deleted. </trashed-message>
    <div class="max-w-3xl bg-white rounded-md shadow overflow-hidden">
      <form @submit.prevent="update">
        <div class="flex flex-wrap -mb-8 -mr-6 p-8">
          <text-input v-model="form.first_name" :error="form.errors.first_name" class="pb-8 pr-6 w-full lg:w-1/2" label="First name" />
          <text-input v-model="form.last_name" :error="form.errors.last_name" class="pb-8 pr-6 w-full lg:w-1/2" label="Last name" />
          <text-input v-model="form.email" :error="form.errors.email" class="pb-8 pr-6 w-full lg:w-1/2" label="Email" />
          <text-input v-model="form.password" :error="form.errors.password" class="pb-8 pr-6 w-full lg:w-1/2" type="password" autocomplete="new-password" label="Password" />
          <select-input v-model="form.owner" :error="form.errors.owner" class="pb-8 pr-6 w-full lg:w-1/2" label="Owner">
            <option :value="true">Yes</option>
            <option :value="false">No</option>
          </select-input>
          <file-input v-model="form.photo" :error="form.errors.photo" class="pb-8 pr-6 w-full lg:w-1/2" type="file" accept="image/*" label="Photo" />
        </div>
        <div class="flex items-center px-8 py-4 bg-gray-50 border-t border-gray-100">
          <button v-if="!user.deleted_at" class="text-red-600 hover:underline" tabindex="-1" type="button" @click="destroy">Delete User</button>
          <loading-button :loading="form.processing" class="btn-indigo ml-auto" type="submit">Update User</loading-button>
        </div>
      </form>
    </div>

    <!-- Passkeys Section -->
    <div class="mt-8 max-w-3xl bg-white rounded-md shadow overflow-hidden p-8">
      <h2 class="text-xl font-bold mb-4">Passkey Management</h2>

      <div v-if="user.passkeys?.length > 0" class="mb-6">
        <h3 class="font-semibold mb-2">Your passkeys:</h3>
        <div class="divide-y border rounded-md">
          <div v-for="passkey in user.passkeys" :key="passkey.id" class="flex justify-between items-center p-4 hover:bg-gray-50">
            <div>
              <p class="font-medium">Name: {{ passkey.name }}</p>
              <p class="text-sm text-gray-500">Last used: {{ passkey.last_used_at || 'Never' }}</p>
            </div>
            <button
              @click="deletePasskey(passkey.id)"
              class="text-red-600 hover:text-red-800 text-sm font-medium"
            >
              Delete
            </button>
          </div>
        </div>
      </div>

      <button
        @click="addPassKey"
        class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
      >
        Add a new passkey
      </button>
    </div>
  </div>
</template>

<script>
import { Head, Link, router } from '@inertiajs/vue3'
import Layout from '@/Shared/Layout.vue'
import TextInput from '@/Shared/TextInput.vue'
import FileInput from '@/Shared/FileInput.vue'
import SelectInput from '@/Shared/SelectInput.vue'
import LoadingButton from '@/Shared/LoadingButton.vue'
import TrashedMessage from '@/Shared/TrashedMessage.vue'

export default {
  components: {
    FileInput,
    Head,
    Link,
    LoadingButton,
    SelectInput,
    TextInput,
    TrashedMessage,
  },
  layout: Layout,
  props: {
    user: Object,
  },
  remember: 'form',
  data() {
    return {
      form: this.$inertia.form({
        _method: 'put',
        first_name: this.user.first_name,
        last_name: this.user.last_name,
        email: this.user.email,
        password: '',
        owner: this.user.owner,
        photo: null,
      }),
    }
  },
  methods: {
    async addPassKey() {
      try {
        // Using direct URL for generating passkey options
        const response = await fetch(`/profile/passkeys/generate-options`);
        console.log(response);
        if (!response.ok) throw new Error('Failed to generate passkey options');

        const options = await response.json();
        console.log(options)
        const startAuthenticationResponse = await window.startRegistration(options);
        console.log(startAuthenticationResponse)

        // Using direct URL for storing passkey
        await this.$inertia.post(
          '/profile/passkeys',
          {
            options: JSON.stringify(options),
            passkey: JSON.stringify(startAuthenticationResponse)
          },
          {
            preserveScroll: true,
            onSuccess: () => {
              this.$inertia.reload();
            },
            onError: (errors) => {
              console.error('Error storing passkey:', errors);
            }
          }
        );
      } catch (error) {
        console.error('Error in passkey operation:', error);
      }
    },

    async deletePasskey(id) {
      if (confirm('Are you SURE you want to delete this passkey?')) {
        await this.$inertia.delete(
          `/profile/passkeys/${id}`,
          {
            preserveScroll: true,
            onSuccess: () => {
              this.$inertia.reload();
            },
            onError: (errors) => {
              console.error('Error deleting passkey:', errors);
            }
          }
        );
      }
    },

    update() {
      this.form.post(`/users/${this.user.id}`, {
        onSuccess: () => this.form.reset('password', 'photo'),
      })
    },
    destroy() {
      if (confirm('Are you sure you want to delete this user?')) {
        this.$inertia.delete(`/users/${this.user.id}`)
      }
    },
    restore() {
      if (confirm('Are you sure you want to restore this user?')) {
        this.$inertia.put(`/users/${this.user.id}/restore`)
      }
    },
  },
}
</script>
