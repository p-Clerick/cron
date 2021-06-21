<?php

class TreeController extends Controller
{
	public function actionRead($level, $id){
		$tree = new Tree;
		if($level){
			$nodes = $tree->getChildNodes($level, $id);
		} else {			
			$nodes = $tree->getRootNode();
		}
		
		// Наступний  рівень дерева
		$level += 1;	
		if($level == Tree::LEVEL_SCHEDULE){
			$leaf = true;
			$cls = 'folder';
		} else {
			$leaf = false;
			$cls = 'folder';
		}
		$res = array();
		foreach($nodes as $node){
			$res[] = array(
				'nodeid' => $node['id'],
				'text' => $node['name'],
				'cls' => $cls,
				'leaf' => $leaf,
				'level' => $level,
			);
		}
		echo CJSON::encode($res);
	}
}