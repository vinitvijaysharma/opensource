<?php
/* Copyright (C) 2002      Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2008 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2009      Regis Houssin        <regis@dolibarr.fr>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
 */

/**
 *	\file       htdocs/adherents/adherent_type.class.php
 *	\ingroup    member
 *	\brief      Fichier de la classe gerant les types d'adherents
 *	\author     Rodolphe Quiedeville
 *	\version    $Id$
 */

require_once(DOL_DOCUMENT_ROOT."/commonobject.class.php");


/**
 *	\class      AdherentType
 *	\brief      Classe gerant les types d'adherents
 */
class AdherentType extends CommonObject
{
	var $error;
	var $errors=array();
	var $db;
	var $table_element = 'adherent_type';

	var $id;
	var $libelle;
	var $statut;
	var $cotisation;  // Soumis a la cotisation
	var $vote;		  // droit de vote
	var $note; 		  // commentaire
	var $mail_valid;  //mail envoye lors de la validation



	/**
	 *  \brief AdherentType
	 *  \param DB				handler acces base de donnees
	 */
	function AdherentType($DB)
	{
		$this->db = $DB ;
		$this->statut = 1;
	}


	/**
	 *  \brief print_error_list
	 */
	function print_error_list()
	{
		$num = sizeof($this->error);
		for ($i = 0 ; $i < $num ; $i++)
		{
			print "<li>" . $this->error[$i];
		}
	}


	/**
	 *  \brief      Fonction qui permet de creer le status de l'adherent
	 *  \param      userid			userid de l'adherent
	 *  \return     > 0 si ok, < 0 si ko
	 */
	function create($userid)
	{
		global $conf;
		
		$this->statut=trim($this->statut);

		$sql = "INSERT INTO ".MAIN_DB_PREFIX."adherent_type (";
		$sql.= "libelle";
		$sql.= ", entity";
		$sql.= ") VALUES (";
		$sql.= "'".addslashes($this->libelle)."'";
		$sql.= ", ".$conf->entity;
		$sql.= ")";

		dol_syslog("Adherent_type::create sql=".$sql);
		$result = $this->db->query($sql);
		if ($result)
		{
			$this->id = $this->db->last_insert_id(MAIN_DB_PREFIX."adherent_type");
			return $this->update();
		}
		else
		{
			$this->error=$this->db->error().' sql='.$sql;
			return -1;
		}
	}


	/**
	 *  \brief      Met a jour en base donnees du type
	 *  \return     > 0 si ok, < 0 si ko
	 */
	function update()
	{
		$this->libelle=trim($this->libelle);

		$sql = "UPDATE ".MAIN_DB_PREFIX."adherent_type ";
		$sql.= "SET ";
		$sql.= "statut = ".$this->statut.",";
		$sql.= "libelle = '".addslashes($this->libelle) ."',";
		$sql.= "cotisation = '".$this->cotisation."',";
		$sql.= "note = '".addslashes($this->note)."',";
		$sql.= "vote = '".$this->vote."',";
		$sql.= "mail_valid = '".addslashes($this->mail_valid)."'";

		$sql .= " WHERE rowid = $this->id";

		$result = $this->db->query($sql);

		if ($result)
		{
			return 1;
		}
		else
		{
			$this->error=$this->db->error().' sql='.$sql;
			return -1;
		}
	}

	/**
	 *	\brief      Fonction qui permet de supprimer le status de l'adherent
	 *	\param      rowid
	 */
	function delete($rowid)
	{

		$sql = "DELETE FROM ".MAIN_DB_PREFIX."adherent_type WHERE rowid = $rowid";

		if ( $this->db->query( $sql) )
		{
			if ( $this->db->affected_rows() )
			{
				return 1;
			}
			else
			{
				return 0;
			}
		}
		else
		{
			print "Err : ".$this->db->error();
			return 0;
		}
	}

	/**
	 *  \brief 		Fonction qui permet de recuperer le status de l'adherent
	 *  \param 		rowid
	 *  \return		int			<0 si KO, >0 si OK
	 */
	function fetch($rowid)
	{
		$sql = "SELECT d.rowid, d.libelle, d.statut, d.cotisation, d.mail_valid, d.note, d.vote";
		$sql .= " FROM ".MAIN_DB_PREFIX."adherent_type as d";
		$sql .= " WHERE d.rowid = ".$rowid;
		
		dol_syslog("Adherent_type::fetch sql=".$sql);

		$resql=$this->db->query($sql);
		if ($resql)
		{
			if ($this->db->num_rows($resql))
			{
				$obj = $this->db->fetch_object($resql);

				$this->id             = $obj->rowid;
				$this->ref            = $obj->rowid;
				$this->libelle        = $obj->libelle;
				$this->statut         = $obj->statut;
				$this->cotisation     = $obj->cotisation;
				$this->mail_valid     = $obj->mail_valid;
				$this->note           = $obj->note;
				$this->vote           = $obj->vote;
			}
			return 1;
		}
		else
		{
			$this->error=$this->db->error();
			return -1;
		}
	}

	/**
	 *  \brief      Return list of members' type
	 *  \return 	array	List of types
	 */
	function liste_array()
	{
		global $conf;
		
		$projets = array();

		$sql = "SELECT rowid, libelle";
		$sql.= " FROM ".MAIN_DB_PREFIX."adherent_type";
		$sql.= " WHERE entity = ".$conf->entity;

		$resql=$this->db->query($sql);
		if ($resql)
		{
			$nump = $this->db->num_rows($resql);

			if ($nump)
			{
				$i = 0;
				while ($i < $nump)
				{
					$obj = $this->db->fetch_object($resql);

					$projets[$obj->rowid] = $obj->libelle;
					$i++;
				}
			}
			return $projets;
		}
		else
		{
			print $this->db->error();
		}

	}


	/**
	 *    	\brief      Renvoie nom clicable (avec eventuellement le picto)
	 *		\param		withpicto		0=Pas de picto, 1=Inclut le picto dans le lien, 2=Picto seul
	 *		\param		maxlen			Longueur max libelle
	 *		\param		option			Page lien
	 *		\return		string			Chaine avec URL
	 */
	function getNomUrl($withpicto=0,$maxlen=0)
	{
		global $langs;

		$result='';

		$lien = '<a href="'.DOL_URL_ROOT.'/adherents/type.php?rowid='.$this->id.'">';
		$lienfin='</a>';

		$picto='group';
		$label=$langs->trans("ShowTypeCard",$this->libelle);

		if ($withpicto) $result.=($lien.img_object($label,$picto).$lienfin);
		if ($withpicto && $withpicto != 2) $result.=' ';
		$result.=$lien.($maxlen?dol_trunc($this->libelle,$maxlen):$this->libelle).$lienfin;
		return $result;
	}

}
?>
