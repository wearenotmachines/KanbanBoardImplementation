# KanbanBoardImplementation
An implementation of my kind of Kanban type of board type of thing

##Talking to the API
Just a simple set of GET and POST endpoints that always return JSON.

##Persistence
data.json is where it all hangs out.

##Endpoints

###`GET /project/{:id}`
- returns a JSON object for the project with :id in this format
```
    {
      "id" : [0-9+],
      "name" : [.+],
      "clientID" : [0-9+],
      "clientName" : [.+],
      "hidden" : [true|false],
      "stickers" : [
        { sticker objects here }
      ]
    },...
```

---

###`GET /active-projects` 
- returns an array of all active projects as project objects.

---

###`GET /project/{:id}/activate` 
- adds the project with :id to the active projects and returns the new active project array
- `/project/4934969/activate`
      
---

###`GET /project/{:id}/deactivate`
- removes the project with :id from the active projects  and returns the new active project array
- `/project/4934969/deactivate`

---

###`POST /project/{:id}/update`
- **param** _project_ : A project object as JSON as above
- replaces the project with :id with the project data passed as the *project* parameter

---

###`POST /project/{:id}/addSticker`
- **param** _sticker_ : A sticker object as JSON in this format
```
   {
      "label": [.+],
      "backgroundColor": [#([0-f]{6})],
      "color": [#([0-f]{6})],
      "icon": [.+]
    }
```
- adds the specified sticker to the project with :id

---

`POST /project/{:id}/remove-sticker`
- **param** _sticker_ : a sticker JSON object
- removes the specified sticker from the project with :id

---

---

###`GET /client/{:id}`
- gets a JSON client object for the client with :id as
```
  {
      "id": [0-9+],
      "name": [.+],
      "projects": [
          { Project JSON Object },
       ]
  }
```
- this gets a client from the data.json store rather than directly from Toggl

---

###`GET /user/{:identifier}/engaged-in`
- gets a Toggl Task object for the user with api token :identifier for the task they are currenty toggling:
```
{
    "data": {
        "id": [0-9+],
        "guid": "56aa191f-a059-4288-868a-f8df3433974b",
        "wid": [0-9+],
        "pid": [0-9+],
        "billable": false,
        "start": "2015-03-07T10:55:28+00:00",
        "duration": -1425725728,
        "description": "Making a toggl client",
        "duronly": false,
        "at": "2015-03-07T10:55:28+00:00",
        "uid": [0-9+]
    }
}
```
- in addition, if the project that this task belongs to is not active, it is added to the active projects array
- _todo_ - add the user to the project and remove them from all others

##Toggl Client data lookup
The following methods return data directly from Toggl.
Toggl requires a user api token to authenticate the request and currently this is hard-coded in the implementation

-`GET /list-clients`
- lists all clients that the authenticating user can see

-`GET /list-users`
- lists all Toggl users that the authenticating user sharesa workspace with

-`GET /list-projects`
- lists all projects in the authenticating user's workspace at Toggl

-`GET /toggl-directory`
- lists the clients and their projects in the authenticating user's workspace
- this is a good call for getting an implementation wide navigation object
