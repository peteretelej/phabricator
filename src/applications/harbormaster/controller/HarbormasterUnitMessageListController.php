<?php

final class HarbormasterUnitMessageListController
  extends HarbormasterController {

  public function handleRequest(AphrontRequest $request) {
    $viewer = $this->getViewer();

    $buildable = id(new HarbormasterBuildableQuery())
      ->setViewer($viewer)
      ->withIDs(array($request->getURIData('id')))
      ->needBuilds(true)
      ->needTargets(true)
      ->executeOne();
    if (!$buildable) {
      return new Aphront404Response();
    }

    $id = $buildable->getID();

    $target_phids = array();
    foreach ($buildable->getBuilds() as $build) {
      foreach ($build->getBuildTargets() as $target) {
        $target_phids[] = $target->getPHID();
      }
    }

    $unit_data = array();
    if ($target_phids) {
      $unit_data = id(new HarbormasterBuildUnitMessage())->loadAllWhere(
        'buildTargetPHID IN (%Ls)',
        $target_phids);
    } else {
      $unit_data = array();
    }

    $unit = id(new HarbormasterUnitSummaryView())
      ->setBuildable($buildable)
      ->setUnitMessages($unit_data);

    $crumbs = $this->buildApplicationCrumbs();
    $this->addBuildableCrumb($crumbs, $buildable);
    $crumbs->addTextCrumb(pht('Unit Tests'));

    $title = array(
      $buildable->getMonogram(),
      pht('Unit Tests'),
    );

    return $this->buildApplicationPage(
      array(
        $crumbs,
        $unit,
      ),
      array(
        'title' => $title,
      ));
  }

}
