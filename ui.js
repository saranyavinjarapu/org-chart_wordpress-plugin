var newTeamDetails = [];
var deleteTeamDetails = [];
let regenerateButtonClick = false;

import {
  dom,
  text,
  subscribe,
  getTree,
  getSecretURL,
  updateSecretURL,
  updateTree,
  createExpandCollapseCallback,
  getExpanded,
} from "./framework.js";

export function ui(tree, url, expanded, element) {
  url = getSecretURL();
  element.parentNode.replaceChild(
    dom(
      "div",
      {
        id: "ui",
      },
      expandCollapseButton(),
      domTree(tree),
      domSecretURL(url),
      dom(
        "button",
        {
          style: "margin-top: 24px;",
          click: () => save(newTeamDetails),
        },
        text("✅ Save")
      )
      //text( '(not yet implemented)' )
    ),
    element
  );
}

export function save(addTeamDetails) {
  //check if the added teams to be saved or deleted teams to be saved exist or not
  if (addTeamDetails.length > 0 || deleteTeamDetails.length > 0) {
    jQuery(document).ready(function ($) {
      $.post(
        "http://saranyafinal.local:10010/wp-content/plugins/org-chart-wordpress-plugin/class-rusty-inc-org-chart-save-data.php",
        {
          addTeamData: addTeamDetails,
          deleteTeamData: deleteTeamDetails,
        },
        function (response) {
          if (response && response != 404) {
            var responseObject = JSON.parse(response);
            var newTreeObject = JSON.parse(responseObject.newTree);
            var newUrlValue = responseObject.newUrl;

            //render updated tree and updated Url
            subscribe((newTree, newURL, newExpanded) =>
              ui(
                newTreeObject,
                newUrlValue,
                newExpanded,
                document.getElementById("ui")
              )
            );

            updateTree(newTreeObject);

            //update secret url if regenerate button has been clicked
            if (regenerateButtonClick == true) {
              updateSecretURL(newUrlValue);
            }

            //empty added team details from the array as teams have been successfully added
            newTeamDetails = [];
            addTeamDetails = [];
            deleteTeamDetails = [];
            regenerateButtonClick = false;
          }
        }
      );
    });
    return true;
  } else {
    window.alert("No changes to save");
    return false;
  }

  /*const form = dom( 'form', { 'method': 'POST', 'action': '' },
    	dom( 'input', { 'name': 'tree', 'type': 'hidden', 'value': JSON.stringify( getTree() ) } ),
    	dom( 'input', { 'name': 'key', 'type': 'hidden', 'value': JSON.stringify( getSecretURL() ) } )
    );	
    document.body.appendChild(form);
    alert('form is' +form);*/
}

//function to add new team items
export function addNewTeamItem(teamItemId) {
  var details = askUserForTeamDetails(teamItemId);

  //check if returned user inputs values are false or present : pass them to the list of teams to be added only if return value exists
  if (details) {
    newTeamDetails.push(details);
    return true;
  } else {
    return false;
  }
}

//function to recursively iterate through the seleted deletion item and retrieve all the child ids
function getIdsToDelete(teamItemObject, outputArray) {
  if (teamItemObject != undefined || teamItemObject != null) {
    var teamItemArray = Array.isArray(teamItemObject)
      ? teamItemObject
      : Object.entries(teamItemObject);

    teamItemArray.forEach(([key, value]) => {
      if (key == "id") {
        outputArray.push(value);
      } else if (key == "children") {
        getIdsToDelete(value[0], outputArray);
      }
    });
  }
  return outputArray;
}

function deleteTeamItem(teamItemObject) {
  var deleteConfirm = askUserForDeleteConfirmation();
  var outputArray = [];

  if (deleteConfirm) {
    var deleteIdsArray = getIdsToDelete(teamItemObject, outputArray);
    deleteTeamDetails = deleteIdsArray;
  }
}

export function askUserForTeamDetails(teamItemId) {
  //alert('get details');
  var parent_id = teamItemId;
  var emoji = prompt("Enter new team’s emoji:");

  //check if emoji value enterted is not empty or a space : dont take next inputs here after
  if (!emoji || emoji == "") {
    alert("Please Enter a valid emoji");
    return false;
  } else {
    var name = prompt("Enter new team’s name:");

    //check if emoji name enterted is not empty or a space

    if (!name || name == "") {
      alert("Please Enter a valid name");
      return false;
    }

    //return user input values only if both emoji and name inputs have a value
    else {
      return { name, emoji, parent_id };
    }
  }
}

function askUserForDeleteConfirmation() {
  return confirm(
    "Are you sure you want to delete the team and all of its subteams?"
  );
}

function expandCollapseButton() {
  const expanded = getExpanded();
  const expandCollapse = createExpandCollapseCallback(
    "#ui > .team",
    ".children",
    15
  );
  return dom(
    "button",
    {
      style: "margin-bottom: 24px;",
      click: expandCollapse,
    },
    text((expanded ? "Collapse" : "Expand") + " tree")
  );
}

function regenerateUrlOnSaveClick() {
  regenerateButtonClick = true;
  document.getElementById("regenerateId").innerHTML =
    "url will be regenerated on save, please click save";
}

function domTree(team, level = 0) {
  const expanded = getExpanded();
  return dom(
    "div",
    {
      class: "team",
      style: `padding-left: ${
        level * 20
      }px; overflow: hidden; position: relative;`,
    },
    dom(
      "div",
      {
        class: "entry",
        style: "z-index: 2; position: relative; background: #f1f1f1;",
      },
      dom(
        "span",
        {
          style: "font-size: 3em;",
        },
        text(team.emoji)
      ),
      text(` ${team.name} `),
      dom(
        "button",
        {
          click: () => addNewTeamItem(team.id),
          title: "Add subteam",
        },
        text("➕")
      ),
      dom(
        "button",
        {
          click: () => deleteTeamItem(team),
          title: "Delete subteam",
        },
        text("🚫")
      )
      /*dom('button', {
            	'click': () => alert('🚧 Deleting teams is not yet implemented'),
            	'title': 'Delete subteam'
            },
            	text('🚫')
            ),*/
    ),
    dom(
      "div",
      {
        class: "children",
        style:
          "z-index: 1; position: relative; display: " +
          (expanded ? "block" : "none"),
      },
      ...Object.keys(team.children).map((id) =>
        domTree(team.children[id], level + 1)
      )
    )
  );
}

function domSecretURL(url) {
  url = getSecretURL();
  return dom(
    "p",
    {},
    text("Secret URL to share: "),
    dom("strong", {}, text(url ? url : "will be regenerated on save")),
    text(" "),
    url
      ? dom(
          "button",
          {
            click: () => regenerateUrlOnSaveClick(),
            title: "Regenerate",
            id: "regenerateId",
          },
          text("🔁")
        )
      : null
  );
}
