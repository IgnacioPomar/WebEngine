<?php
require_once 'ColumnFormatter.php';
require_once 'AutoForm.php';

// YAGNI: Include it in the ColumnFormatter class
class FormatterColumnToCheckbox
{


	/**
	 *
	 * @param mixed $val
	 * @param string $class
	 * @return string
	 */
	public function getSpan ($val, $class)
	{
		$retVal = "<div class='$class'>";

		$attributes = [ 'disabled'];
		if ($val)
		{
			$attributes [] = 'checked';
		}

		$retVal .= '<input type="checkbox"' . join (' ', $attributes) . '>';
		$retVal .= '</div>';
		return $retVal;
	}
}

class Groups
{
	private $mysqli;
	private $jsonFile;
	private $uriPrefix;


	private function __construct ($mysqli)
	{
		$this->mysqli = $mysqli;
		$this->jsonFile = $GLOBALS ['basePath'] . 'src/tables/groups.jsonTable';
		$this->uriPrefix = $_SERVER ["REQUEST_URI"];
	}

	// @formatter:off
	const COLS_TABLE_GROUPS = array (
			'grpName'		=> array ('w-400', 'Group name', 'Visible name on the platform.'),
	);
	// @formatter:on

	/**
	 *
	 * @return string
	 */
	private function showListGroups ()
	{
		$query = $this->getQueryGroups ();
		$resultGroups = $this->mysqli->query ($query);

		$formatter = new ColumnFormatter (self::COLS_TABLE_GROUPS);

		$retVal = "<a href='{$this->uriPrefix}&newGroup' class='btn mb-3'>Add new group</a>";
		$retVal .= "<div class='head'>{$formatter->getHeaderCols ()}</div>";

		while ($group = $resultGroups->fetch_assoc ())
		{
			$retVal .= '<div class="line">';

			$retVal .= $formatter->getStyledBodyCols ($group);

			$retVal .= '<div class="w-100"></div>';

			$link = "{$this->uriPrefix}&idGrp={$group['idGrp']}";
			$retVal .= "<a href='$link'><span class='w-50'><svg xmlns='http: // www.w3.org/2000/svg' width='16' height='16' fill='currentColor' class='bi bi-pencil-fill' viewBox='0 0 16 16'><path d='M12.854.146a.5.5 0 0 0-.707 0L10.5 1.793 14.207 5.5l1.647-1.646a.5.5 0 0 0 0-.708l-3-3zm.646 6.061L9.793 2.5 3.293 9H3.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.207l6.5-6.5zm-7.468 7.468A.5.5 0 0 1 6 13.5V13h-.5a.5.5 0 0 1-.5-.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.5-.5V10h-.5a.499.499 0 0 1-.175-.032l-.179.178a.5.5 0 0 0-.11.168l-2 5a.5.5 0 0 0 .65.65l5-2a.5.5 0 0 0 .168-.11l.178-.178z'/></svg></span></a>";

			$retVal .= "</div>";
		}

		return $retVal;
	}


	/**
	 *
	 * @param number $idGroup
	 * @return string
	 */
	private function getQueryGroups ($idGroup = 0)
	{
		// YAGNI: Receive an array with the necessary columns
		$query = 'SELECT * FROM weGroups';
		if ($idGroup != 0)
		{
			$query .= " WHERE idGrp = $idGroup";
		}
		return $query;
	}


	/**
	 *
	 * @return string
	 */
	private function showGroupForm ()
	{
		$autoForm = new AutoForm ($this->jsonFile);
		$fieldSet = array ('grpName');
		// It is necessary to generate the form
		$autoForm->set = $fieldSet;

		$retVal = '';

		if (! empty ($_POST))
		{
			if (! empty ($_POST ['idGrp']))
			{
				$retVal .= $this->updateGroup ($autoForm);
			}
			else if (! empty ($_POST ['grpName']))
			{
				return $this->insertNewGroup ($autoForm);
			}
		}

		if (! empty ($_GET ['idGrp']))
		{
			$query = $this->getQueryGroups ($_GET ['idGrp']);
			if ($resultGroup = $this->mysqli->query ($query))
			{
				if ($group = $resultGroup->fetch_assoc ())
				{
					$autoForm->setHidden ('idGrp', $group ['idGrp']);

					if (empty ($retVal))
					{
						$retVal .= '<div class="container"><h1>Edit group</h1>';
					}
					$retVal .= $autoForm->generateForm ($group, ! empty ($_POST ['idGrp']));
					$retVal .= '</div>';

					return $retVal;
				}
			}
		}

		$retVal = '<div class="container"><h1>Add new group</h1>';
		$retVal .= $autoForm->generateForm ($_POST);
		$retVal .= '</div>';

		return $retVal;
	}


	/**
	 *
	 * @param object $autoForm
	 * @return string
	 */
	private function insertNewGroup ($autoForm)
	{
		$autoForm->mysqli = $this->mysqli;
		$query = $autoForm->getInsertSql ([ ]);
		$this->mysqli->query ($query);

		$retVal = '<p>Registro Insertado Correctamente</p>';
		$icon = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-counterclockwise" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M8 3a5 5 0 1 1-4.546 2.914.5.5 0 0 0-.908-.417A6 6 0 1 0 8 2v1z"/><path d="M8 4.466V.534a.25.25 0 0 0-.41-.192L5.23 2.308a.25.25 0 0 0 0 .384l2.36 1.966A.25.25 0 0 0 8 4.466z"/></svg>';
		$retVal .= '<div class="container"><a class="btn rigth" href="' . strtok ($this->uriPrefix, '&') . '">' . $icon . 'Volver</a></div>';
		return $retVal;
	}


	/**
	 *
	 * @param object $autoForm
	 * @return string
	 */
	private function updateGroup ($autoForm)
	{
		$autoForm->mysqli = $this->mysqli;
		$sql = $autoForm->getUpdateSql ([ ], [ 'idGrp']);

		$this->mysqli->query ($sql);

		$retVal = "<p>Registro Actualizado Correctamente</p>";
		$icon = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-counterclockwise" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M8 3a5 5 0 1 1-4.546 2.914.5.5 0 0 0-.908-.417A6 6 0 1 0 8 2v1z"/><path d="M8 4.466V.534a.25.25 0 0 0-.41-.192L5.23 2.308a.25.25 0 0 0 0 .384l2.36 1.966A.25.25 0 0 0 8 4.466z"/></svg>';
		$retVal .= '<div class="container"><a class="btn rigth" href="' . strtok ($this->uriPrefix, '&') . '">' . $icon . 'Volver</a></div>';

		return $retVal;
	}


	/**
	 *
	 * @param mysqli $mysqli
	 * @return string
	 */
	public static function main ($mysqli)
	{
		$groups = new Groups ($mysqli);

		if (isset ($_GET ['newGroup']) || ! empty ($_GET ['idGrp']))
		{
			$retVal = $groups->showGroupForm ();
		}
		else
		{
			$retVal = $groups->showListGroups ();
		}

		$retVal = str_replace ('@@content@@', $retVal, file_get_contents ($GLOBALS ['basePath'] . 'src/rsc/html/defaultView.htm'));

		return $retVal;
	}
}