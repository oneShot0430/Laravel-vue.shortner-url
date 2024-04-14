<template>
  <div class="url-list-container">
    <h2 class="text-center page-title">URLs List</h2>

    <div class="table-responsive">
      <table class="table table-hover">
        <thead class="thead-dark">
          <tr>
            <th scope="col">ID</th>                
            <th scope="col">Original URL</th>
            <th scope="col">Shortened URL</th>                
            <th scope="col">Actions</th>                
          </tr>
        </thead>
        <tbody>
          <tr v-for="(url, index) in urls" :key="url.id">
            <th scope="row">{{ index + 1 }}</th>                
            <td><a :href="url.old_url" target="_blank">{{ url.old_url }}</a></td>
            <td><a :href="url.new_url" target="_blank">{{ url.new_url }}</a></td>
            <td>
              <div class="btn-group" role="group">                     
                <button type="button" class="btn btn-danger" @click="deleteUrl(url.id)">Delete</button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
 
<script>
    export default {
        data() {
            return {
                urls: []
            }
        },
        created() {
            this.axios
                .get(process.env.MIX_APP_URL+'/api/urls/')
                .then(response => {
                    this.urls = response.data;
                });
        },
        methods: {
            deleteUrl(id) { 
                this.axios
                    .delete(process.env.MIX_APP_URL+`/api/urls/${id}`)
                    .then(response => {
                        let i = this.urls.map(data => data.id).indexOf(id);
                        this.urls.splice(i, 1)
                    });
            }
        }
    }
</script>