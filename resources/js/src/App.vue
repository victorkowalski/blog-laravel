<!--template>
    <div class="jumbotron">
        <div class="container">
            <div class="row">
                <div class="col-sm-6 offset-sm-3">
                    <div v-if="alert.message" :class="`alert ${alert.type}`">{{alert.message}}</div>
                    <router-view></router-view>
                </div>
            </div>
        </div>
    </div>
</template-->
<template>
  <div id="app">
    <real-world-header />
    <router-view></router-view>
    <!--real-world-footer /-->
  </div>
</template>

<script>
import RealWorldHeader from '../components/Header';
//import RealWorldFooter from '@/components/Footer';


export default {
  name: 'app',
  components: {
    RealWorldHeader, /*RealWorldFooter,*/
  },
};
</script>

<style>
</style>

<!--script>
//import { mapState, mapActions } from 'vuex'

export default {
    name: 'app',
    computed: {
        /*...mapState({
            alert: state => state.alert
        })*/
    },
    methods: {
        ...mapActions({
            clearAlert: 'alert/clear' 
        })
    },
    watch: {
        $route (to, from){
            // clear alert on location change
            this.clearAlert();
        }
    } 
};
</script-->