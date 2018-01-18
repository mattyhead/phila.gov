<template>
  <div id="events">
    <form v-on:submit.prevent>
      <div class="search">
        <input id="post-search" type="text" name="search"
        placeholder="Begin typing to filter by title" class="search-field" ref="search-field"
        v-model="searchedVal">
        <input type="submit" value="submit" class="search-submit">
      </div>
    </form>
    <div id="filter-results" class="bg-ghost-gray pam mbm">
      <div class="h5">Filter results</div>
      <div class="grid-x grid-margin-x">
        <div class="cell medium-8 small-11">
          <datepicker
          placeholder="Start date"
          name="startDate"
          v-on:closed="runDateQuery"
          v-model="state.startDate"
          format="MMM. dd, yyyy"></datepicker>
        </div>
        <div class="cell medium-1 small-2 mts">
          <i class="fa fa-arrow-right"></i>
        </div>
        <div class="cell medium-8 small-11">
          <datepicker
          name="endDate"
          placeholder="End date"
          v-on:closed="runDateQuery"
          v-model="state.endDate"
          format="MMM. dd, yyyy"></datepicker>
        </div>
        <div class="cell medium-7 small-24">
          <a class="button content-type-featured full" @click="reset">Clear filters</a>
        </div>
      </div>
    </div>
    <div v-show="loading" class="mtm center">
      <i class="fa fa-spinner fa-spin fa-3x"></i>
    </div>
    <div v-show="emptyResponse" class="h3 mtm center">Sorry, there are no results.</div>
    <div v-show="failure" class="h3 mtm center">Sorry, there was a problem. Please try again.</div>
    <div v-show="!loading && !emptyResponse && !failure">
      <div v-for="(event, index) in filteredEvents"
        :key="event.id">
          <div v-if="event.id" class="event-container">
            <div class="grid-x grid-padding-x event-row medium-collapse"
            @click="$modal.show(event.id)">
              <div class="small-6 medium-3 cell calendar-date pam">
                <div class="align-self-middle">
                  <div class="month">
                    <span v-if="event.start.dateTime">{{ event.start.dateTime | formatMonth }}</span>
                    <span v-else>{{ event.start.date | formatMonth }}</span>
                  </div>
                  <div class="day">
                    <span v-if="event.start.dateTime">{{event.start.dateTime | formatDay}}</span>
                    <span v-else>
                      {{event.start.date | formatDay }}
                    </span>
                  </div>
                </div>
              </div>
              <div class="small-18 medium-21 cell calendar-details pam">
                <div class="post-label post-label--calendar"><i class="fa fa-calendar-o fa-lg" aria-hidden="true"></i>
                  <span>Event</span>
                </div>
                <div class="title">{{event.summary}}</div>
                <div
                v-if="event.start.dateTime"
                class="start-end">
                  {{event.start.dateTime | formatTime }} to {{event.end.dateTime | formatTime }}
                </div>
                <div v-else>
                  All day
                </div>
                <div class="location">{{event.location}}</div>
              </div>
            </div>
          </div>
      </div>
    </div>
      <div v-for="(event, index) in events"
        :key="event.id">
        <modal
        :name="event.id"
        height="auto"
        :adaptive="adaptive"
        :scrollable="true">
        <div class="v--modal-container">
          <div slot="top-right">
            <button @click="$modal.hide(event.id)" class="close-button" type="button" aria-label="Close modal">
              <span aria-hidden="true">×</span>
            </button>
          </div>
          <div class="post-label post-label--calendar"><i class="fa fa-calendar-o fa-lg" aria-hidden="true"></i>
            <span>Event</span>
          </div>
          <h3>{{event.summary}}</h3>
          <div class="location mvm">{{event.location}}</div>
          <div
          v-if="event.start.dateTime"
          class="start-end mvm">
          {{event.start.dateTime | formatDate }}<br />
            {{event.start.dateTime | formatTime }} to {{event.end.dateTime | formatTime }}<br />
          </div>
          <div class="mvm" v-else>
            {{event.start.date | formatDate }}<br />
            All day
          </div>
          <div class="mbm">
            <div v-html="event.description"></div>
          </div>
          <div class="post-meta mbm reveal-footer"></div>
        </div>
      </modal>
    </div>
  </div>
</template>

<script>
import moment from 'moment'
import axios from 'axios'
import vSelect from 'vue-select'
import Datepicker from 'vuejs-datepicker';
import Search from './components/phila-search.vue'

const gCalEndpoint = 'https://www.googleapis.com/calendar/v3/calendars/'
const links = []

const gCalId = g_cal_id

export default {
  name: 'events-archive',
  components: {
    vSelect,
    Datepicker,
    'phila-search': Search
  },
  data: function() {
    return{
      calendars: JSON.parse(g_cal_data.json),
      calData: [{}],

      events: [{
        summary: '',
        start: {
          dateTime: '',
          date: ''
        },
        end: {
          dateTime: '',
          date: ''
        },
      }],

      //selectedCategory: '',

      search: '',
      searchedVal: '',

      loading: false,
      emptyResponse: false,
      failure: false,

      state: {
        loadStartDate: moment().format(),
        loadEndDate: moment().format(),
        startDate: '',
        endDate: '',
      },

      //queriedCategory: this.$route.query.category

    }
  },
  filters: {
    'formatMonth': function(value) {
      if (value) {
        return moment( String(value) ).format('MMM')
      }
    },
    'formatDay': function(value) {
      if (value) {
        return moment( String(value) ).format('D')
      }
    },
    'formatDate': function(value) {
      if (value) {
        return moment( String(value) ).format('MMMM DD, YYYY')
      }
    },
    'formatTime': function(value) {
      if (value) {
        return moment( String(value) ).format('LT')
      }
    },
  },
  mounted: function () {
    this.getUpcomingEvents()
    this.sortedItems(this.events)
    //this.getDropdownCategories()
    this.loading = true
  },
  methods: {
    getUpcomingEvents: function () {
      for( var i = 0; i < this.calendars.length; i++ ){
        links.push(gCalEndpoint + this.calendars[i] + '/events/?key=' + gCalId + '&maxResults=10&singleEvents=true&timeMin=' + moment().format() )
      }
      axios.all( links.map( l => axios.get( l ) ) )
        .then(response =>  {
          this.calData = response
          const temp = []

          for (var j = 0; j < this.calData.length; j++ ){
            for(var k = 0; k < response[j].data.items.length; k++) {
              this.events.push(response[j].data.items[k])
           }
          }
          this.successfulResponse
        })
        .catch( e => {
          this.failure = true
          this.loading = false
        })

    },
    onSubmit: function (event) {
      this.loading = true

      this.$nextTick(function () {
        axios.get(gCalEndpoint + 'archives', {
          params : {
            's': this.searchedVal,
            'category': this.selectedCategory,
            'count': -1,
            'start_date': this.state.startDate,
            'end_date': this.state.endDate,
            }
          })
          .then(response => {
            this.events = response.data
            this.successfulResponse
          })
          .catch(e => {
            this.failure = true
            this.loading = false
        })
      })
    },
    reset() {
      window.location = window.location.pathname;
      //Object.assign(this.$data, this.$options.data.call(this));

      /*this.selectedCategory = ''
      axios.get(gCalEndpoint + 'archives', {
       params : {
          'count': -1
        }
      })
        .then(response => {
          this.events = response.data
          this.loading = false
          this.searchedVal = ''
          this.checkedTemplates = ''
          this.selectedCategory = ''
          this.state.startDate = ''
          this.state.endDate = ''
        })
        .catch(e => {
          this.failure = true
      })
      this.$forceUpdate();
      */
    },
    runDateQuery(){
      if ( !this.state.startDate || !this.state.endDate )
        return;

      //reset data
      this.events = [{
        summary: '',
        start: {
          dateTime: '',
          date: ''
        },
        end: {
          dateTime: '',
          date: ''
        }
      }]

      //reset links
      const links = []

      for( var i = 0; i < this.calendars.length; i++ ){
        links.push(gCalEndpoint + this.calendars[i] + '/events/?key=' + gCalId + '&maxResults=20&singleEvents=true&timeMin=' + moment(String(this.state.startDate)).format() + '&timeMax=' + moment(String(this.state.endDate)).format() )
      }
      axios.all( links.map( l => axios.get( l ) ) )
        .then(response =>  {
          this.calData = response
          const temp = []

          for (var j = 0; j < this.calData.length; j++ ){
            for(var k = 0; k < response[j].data.items.length; k++) {
              this.events.push(response[j].data.items[k])
           }
          }
          this.successfulResponse
          this.loading = false

        })
        .catch( e => {
          this.failure = true
        })

    },
    filteredList: function ( list, searchedVal ) {
      const searched = this.searchedVal.trim();
      return list.filter((event) => {
        if (typeof event.summary === 'undefined'){
          return
        }else{
          return event.summary.toLowerCase().indexOf(searched.toLowerCase()) > -1
        }
      })
    },
    sortedItems: function ( list ) {
      return list.sort((a, b) => {
        if (a.start.dateTime) {
          return moment(a.start.dateTime) - moment(b.start.dateTime)
        }else {
          return moment(a.start.date) - moment(b.start.date)
        }
      })
    }
 },
  computed:{
    filteredEvents: function(){
      return this.sortedItems(this.filteredList(this.events, this.searchedVal) )
    },
    successfulResponse: function(){
      if (this.events.length == 0) {
        this.emptyResponse = true
        this.loading = false
        this.failure = false
      }else{
        this.emptyResponse = false
        this.loading = false
        this.failure = false
      }
    },
  },
}
</script>

<style>
@media screen and (max-width: 39.9375em) {
  .v--modal-overlay.scrollable .v--modal-box{
    width:100% !important;
    height:100% important;
  }
}
.v--modal-container{
  position: relative;
}
.v--modal-container button{
  background: transparent;
  padding:0;
}
.v--modal-overlay{
  z-index:9990 !important;
}
.v--modal-overlay .v--modal-box{
  border-bottom:5px solid green;
  padding:1rem;
}
.v--modal{
  border-radius: 0;
  box-shadow:none;
}
.vdp-datepicker [type='text'] {
  height: 2.4rem;
}
.vdp-datepicker input:read-only{
  background: white;
  cursor: pointer;
}
#archive-results .vdp-datepicker__calendar .cell.selected,
#archive-results .vdp-datepicker__calendar .cell.selected.highlighted,
#archive-results .vdp-datepicker__calendar .cell.selected:hover{
  background: #25cef7;
}

</style>