<?php

class Rede {
	var $labels;
	var $ligacoes=array();
	var $excluirHubsGrau=10000;
	var $excluirCitacoes=10000;
	
	public function addLigacaoByLabel($nome1, $nome2) {
		$id1 = $this->getIdLabel($nome1);
		$id2 = $this->getIdLabel($nome2);
		if ($id1 == 0 ) return false;
		if ($id2 == 0 ) return false;
		$this->addLigacao($id1, $id2);
	}

	public function addLigacao($id1, $id2) {
		$ligacao[0] = intval($id1);
		$ligacao[1] = intval($id2);
		$this->labels[intval($id1)]['grau']++;
		$this->labels[intval($id2)]['grau']++;
		array_push($this->ligacoes, $ligacao);
	}

	public function getIdLabel($nome) {
		for ($i=1;$i<=sizeof($this->labels);$i++) {
			if ($nome == $this->labels[$i]['nome']) {
				//$this->labels[$i]['ocorrencias']++;
				return $i;
			}
		}
		return 0;
	}

	public function incOcorrencia($nome) {
		$id = $this->getIdLabel($nome);
		$this->labels[$id]['ocorrencias']++;
	}
	
	
	public function getOcorrencias($id) {
		return $this->labels[$id]['ocorrencias'];
	}
	
	public function getGrau($id) {
		return $this->labels[$id]['grau'];
	}
	

	public function getOcorrenciasByName($nome) {
		return $this->getOcorrencias($this->getIdLabel($nome));
	}

	public function addLabel($nome,$img='nofoto.jpg',$peso=0) {
		$nome = html_entity_decode( utf8_encode( $nome));
		$encontrado = 0;
		for ($i=1;$i<=sizeof($this->labels);$i++) {
			if ($nome == $this->labels[$i]['nome']) {
				$encontrado = $i;
				break;
			}
		}
		
		if ($encontrado == 0) {
			$this->labels[$i]['nome'] = $nome;
			$this->labels[$i]['ocorrencias'] = $peso;
			$this->labels[$i]['grau']=0;
			$encontrado = $i;
		}
		
		if ((file_exists('imagens/'.$img)) && ($img != '') && (isset($img))) {
			$this->labels[$encontrado]['img'] = $img;
		} else {
			//if (!isset($this->labels[$encontrado]['img']))
			$this->labels[$encontrado]['img'] = 'nofoto.jpg';
		}
		
	}

	public function viewVIVA() {
		ob_clean();
		?>
		<!DOCTYPE html>
		<html>
			<head>
				<title>Rede Complexa</title>
				<script type="text/javascript" src="plugin/vivagraph.min.js"></script>
			    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
				<script type="text/javascript">
					function main() {
						// Step 1. We create a graph object.
						var graph = Viva.Graph.graph();
						<?php
						//var_dump($this->labels); exit;
						for ($i=1;$i<=sizeof($this->labels);$i++) {
							$ocorrencias = $this->labels[$i]['ocorrencias'];
							if (!isset( $ocorrencias)) $ocorrencias = 0;
							echo "graph.addNode('".$i."', {
								id:$i,
								
								nome:'". ($this->labels[$i]['nome'])."',
								img:'".$this->labels[$i]['img']."',
								ocorrencias:".$ocorrencias.",
								grau:".$this->labels[$i]['grau']."
							});\r\n";
						}
						?>

			            var graphics = Viva.Graph.View.svgGraphics();
			
			            // This function let us override default node appearance and create
			            // something better than blue dots:
			            graphics.node(function(node) {
			                // node.data holds custom object passed to graph.addNode():
			                var url = 'imagens/' + node.data.img;
							var tam = (node.data.ocorrencias)*1;
							if (tam > 200) tam = 200;
			                var ui = Viva.Graph.svg('image')
			                     .attr('width', 20+tam)
			                     .attr('height', 20+tam)
			                     .link(url);
			                     
							$(ui).click(function() { // mouse click
			                    console.log(node);
			                    alert(node.data.nome+' (Grau: '+node.data.grau+') '
			                    );
			                });
			                		                     
			                return ui;
			            });


						// Step 2. We add nodes and edges to the graph:
						
						<?php
						for ($i = 0; $i < sizeof($this->ligacoes); $i++) {
							$val1 = $this->ligacoes[$i][0];
							$val2 = $this->ligacoes[$i][1];
							if (!$this->filtraPar($val1, $val2)) {
								echo "graph.addLink($val1, $val2);";
							}
						}
						?>
		
						/* Note: graph.addLink() creates new nodes if they are not yet
						present in the graph. Thus calling this method is equivalent to:
		
						graph.addNode(1);
						graph.addNode(2);
						graph.addLink(1, 2);
						*/
		
						// Step 3. Render the graph.
						var renderer = Viva.Graph.View.renderer(graph, {
		                    graphics : graphics
		                });
						renderer.run();
					}
				</script>
		
				<style type="text/css" media="screen">
					html, body, svg {
						width: 100%;
						height: 100%;
					}
				</style>
			</head>
			<body onload='main()'>
		
			</body>
		</html>
		<?php
	}

	//verifica se um parte deve se excluido da lista baseado no filtro de grau maximo
	private function filtraPar($id1,$id2) {

		if ($this->filtra($id1)) {
			return true;
		}
		
		if ($this->filtra($id2)) {
			return true;
		}
		
		return false;
	} 
	
	private function filtra($id) {
		$qtd = $this->getGrau($id);
		if ($qtd > $this->excluirHubsGrau) {
			return true;
		}
		
		
		$qtd = $this->getOcorrencias($id);
		if ($qtd > $this->excluirCitacoes) {
			return true;
		}

		return false;
	}


	 function viewListaAsHtml($array, $table = true) {
		//if (!$array) $array = $this->adjacenciaArray;
		$out = '';
		$i = 0;
		foreach ($array as $key => $value) {
			if (is_array($value)) {
				if (!isset($tableHeader)) {
					$tableHeader = '<th>' . implode('</th><th>', array_keys($value)) . '</th>';
				}
				array_keys($value);
				$out .= '<tr>';
				$out .= $this -> array2Html($value, false);
				$out .= '</tr>';
			} else {
				$out .= "<td bgcolor='#" . dechex(255 - $value) . dechex(255 - $value) . dechex(255 - $value) . "' >$value</td>";
				//$out .= "<td>$i:$value</td>";
			}
			$i++;
		}

		if ($table) {
			return '<table>' . $tableHeader . $out . '</table>';
		} else {
			return $out;
		}
	}	
	

	function geraTXT($separador=',' , $labels=true) {
		ob_clean();
		header("Content-Type: text/plain");
		if ($labels) {
			echo "-------------------------------\r\n";
			echo " LABELS \r\n";
			echo "-------------------------------\r\n";
			for ($i=1;$i<=sizeof($this->labels);$i++) {
				echo $i.','.$this->labels[$i]['nome'].','.$this->labels[$i]['img'].','.$this->labels[$i]['ocorrencias']."\n";
			}
		}
		
		echo "-------------------------------\r\n";
		echo " DADOS \r\n";
		echo "-------------------------------\r\n";
		foreach ($this->ligacoes as $pares) {
			echo trim($pares[0]).$separador.trim($pares[1])."\n";
		}
		
		return true;
	}
	
	function loadTXT($arq) {
		$file = file_get_contents( $arq);
		$fa = explode("\n",$file);
		if (substr($fa[0], 1,1) != '-') {
			echo "Arquivo com formato inválido, primeira (".trim(substr($fa[0], 1,1)).") linha deve ter --------";
			exit;
		}
		$linLabels = 0;
		$linDados = 0;
		if (trim(substr($fa[1], 0,15)) == 'LABELS') {
			$linLabels = 1;
		} else if (trim(substr($fa[1], 0,15)) == 'DADOS') {
			$linDados = 1;
		} else {
			echo "Arquivo com formato inválido, segunda linha (".trim(substr($fa[1], 0,15)).") deve ter LABELS ou DADOS";
			exit;
		}
		if (substr($fa[2], 0,1) != '-') {
			echo "Arquivo com formato inválido, terceira linha deve ter --------";
			exit;
		}
		
		if ($linLabels > 0) {
			for ($i=$linLabels+2;$i<sizeof($fa);$i++) {
				$fa[$i] = str_replace("\r", '', $fa[$i]);
				if (substr($fa[$i], 1,1) == '-') {
					$linDados = $i+1;
					break;
				}
				$label = explode(',',$fa[$i]);
				if ($label[0] != ($i-2)) {
					echo "ID deve ser sequêncial iniciando em 1."; exit;
				}
				if (!isset($label[3])) $label[3]=0;
				if (!isset($label[2])) $label[2]='';
				$this->addLabel($label[1] . " (ID $i)",$label[2],$label[3]);
				/*
				if ($i==48) {
					echo $label[1];
					exit;
				}
				*/
			}
		}
		//var_dump($this->labels); exit;
		for ($i=$linDados+2; $i<sizeof($fa);$i++) {
			$dados = explode(',',$fa[$i]);
			if (isset($dados[1]))
				$this->addLigacao($dados[0], $dados[1]);
		}

				//var_dump($fa);
		//echo $file;
	}

	
	function exportGEXF() {
		ob_clean();
		header('Content-Type: application/xml');
		echo '<?xml version="1.0" encoding="UTF-8"?>
				<gexf xmlns="http://www.gexf.net/1.2draft" xmlns:viz="http://www.gexf.net/1.1draft/viz" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.gexf.net/1.2draft http://www.gexf.net/1.2draft/gexf.xsd" version="1.2"> version="1.2">
				<meta lastmodifieddate="2009-03-20">
					<creator>
						Gexf.net
					</creator>
					<description>
						Rede Complexa
					</description>
				</meta>
		';
		//mode="static" defaultedgetype="undirected"
		echo '<graph defaultedgetype="undirected" >
			<nodes>';
			$jpos = 1;
			$ipos = 1;
			for ($i = 1; $i <= sizeof($this->labels); $i++) {
				echo '<node id="' . $i . '" label="' .  utf8_encode($this->labels[$i]['nome']) . '" >';
	 				echo '<viz:color r="0" g="0" b="0" a="0.6"/>';
	                //echo '<viz:position x="'.($jpos*40).'" y="'.($ipos*40).'" z="0.0"/>';
	                echo '<viz:size value="'.$this->labels[$i]['ocorrencias'].'"/>';
	                echo '<viz:shape value="disc"/>';
				echo '</node>';
				$jpos++;
				if ($jpos > 38) {
					$jpos = 1;
					$ipos --;
				}
			}
		echo '</nodes>
		
		
		
			<edges>
		';
		for ($i = 0; $i < sizeof($this->ligacoes); $i++) {//
			echo '<edge id="' . $i . '" source="' . $this -> ligacoes[$i][0] . '" target="' . $this -> ligacoes[$i][1] . '" />';
		}
		echo ' </edges>
			</graph>
			</gexf>';
	}

	
}

