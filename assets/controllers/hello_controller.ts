import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
  static targets = ["output"];

  declare readonly outputTarget: HTMLElement;

  connect() {
    console.log("Hello Stimulus (TypeScript) controller connected!");
  }

  greet() {
    this.outputTarget.textContent = "Bonjour depuis Stimulus + TypeScript !";
  }
}