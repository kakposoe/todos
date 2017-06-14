# To Do Command Line App

A quick way of noting down your to do's on your current project.

## Well, is it a normal todo list?
Pretty much. Only difference is its completely on the command line. 

## How does it work?
running <code>todo init</code> will create a todo.json file in the folder you are currently in. Then use the app to create new todo tasks.

You can even create sub tasks.
<code>todo 'This is a subtask' -s 2</code>

#I don't want to add them one by one
Just simply add 'and' after each todo
<code>todo 'This' and 'That' and 'This'</code>

### Things to work on
- Check if indexes are numeric
- Check functions to see if check if exists ??
- Check whether or not all sub tasks have been completed when marking subtask as complete, and auto update top level task to completed
- Have a user confirm if they would like to remove task with sub tasks, if deleting parent level task with subtasks
- Convert sublevel tasks to top level tasks
- Add 'No More tasks' if all task have been removed
- Multilevel child tasks
- Deadline keys and visual representation of tasks past their deadline
- Call task by index
- Add High Priority Indicator
- List all tasks not completed
- Chain removal of events e.f. todo remove 2 and 3 and 4 and 5

